import 'package:flutter/material.dart';
import '../models/establishment.dart';
import '../models/user.dart';
import '../services/database_helper.dart';
import '../services/sync_service.dart';
import '../services/network_service.dart';
import 'registration_screen.dart';
import 'detail_screen.dart';
import 'login_screen.dart';
import 'unpaid_taxes_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _dbHelper = DatabaseHelper();
  final _syncService = SyncService();
  List<Establishment> _establishments = [];
  User? _activeUser;
  bool _isSyncing = false;
  String _selectedFilter = 'all'; // 'all', 'synced', 'pending_sync', 'invalid'
  bool _hasAutoPulled = false;

  @override
  void initState() {
    super.initState();
    _loadActiveUser();
    _loadEstablishments();
  }

  void _loadActiveUser() async {
    final user = await _dbHelper.getActiveUser();
    setState(() => _activeUser = user);
  }

  void _loadEstablishments() async {
    final establishments = await _dbHelper.getAllEstablishments();
    setState(() => _establishments = establishments);
    if (establishments.isEmpty && NetworkService().isOnline.value && !_hasAutoPulled) {
      _hasAutoPulled = true;
      _handleSyncAndPull();
    }
  }

  Future<void> _handleSyncAndPull() async {
    if (_isSyncing) return;
    setState(() => _isSyncing = true);
    try {
      // 1. Sync local pending/failed registrations
      await _syncService.performSync();
      // 2. Pull latest status from the server
      await _syncService.fetchAndUpdateStatuses();
      _loadEstablishments();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Sync and status check completed successfully.'),
          backgroundColor: Color(0xFF0A5C36),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Synchronization completed with errors: $e'),
          backgroundColor: Colors.red[800],
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isSyncing = false);
      }
    }
  }

  void _handleLogout() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Confirm Logout'),
          content: const Text('Are you sure you want to log out of the system?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
            ),
            TextButton(
              onPressed: () {
                Navigator.pop(context); // close dialog
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(builder: (context) => const LoginScreen()),
                );
              },
              child: const Text('Logout', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
            ),
          ],
        );
      },
    );
  }

  int get countAll => _establishments.length;
  int get countSynced => _establishments.where((e) => e.syncStatus == 'synced').length;
  int get countPending => _establishments.where((e) => e.syncStatus == 'pending_sync' || e.syncStatus == 'failed').length;
  int get countInvalid => _establishments.where((e) => e.syncStatus == 'invalid').length;

  List<Establishment> get _filteredEstablishments {
    if (_selectedFilter == 'all') {
      return _establishments;
    } else if (_selectedFilter == 'synced') {
      return _establishments.where((e) => e.syncStatus == 'synced').toList();
    } else if (_selectedFilter == 'pending_sync') {
      return _establishments.where((e) => e.syncStatus == 'pending_sync' || e.syncStatus == 'failed').toList();
    } else if (_selectedFilter == 'invalid') {
      return _establishments.where((e) => e.syncStatus == 'invalid').toList();
    }
    return _establishments;
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredEstablishments;
    const primaryGreen = Color(0xFF0A5C36);
    const goldenAccent = Color(0xFFC5A059);

    return Scaffold(
      backgroundColor: const Color(0xFFF4F6F8),
      appBar: AppBar(
        title: const Text(
          'URCS Dashboard',
          style: TextStyle(fontWeight: FontWeight.bold, letterSpacing: 0.5),
        ),
        elevation: 0,
        actions: [
          ValueListenableBuilder<bool>(
            valueListenable: NetworkService().isOnline,
            builder: (context, isOnline, child) {
              return Container(
                margin: const EdgeInsets.symmetric(vertical: 12),
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: isOnline ? Colors.green[800]!.withOpacity(0.2) : Colors.red[800]!.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isOnline ? Colors.greenAccent : Colors.redAccent,
                    width: 1,
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 6,
                      height: 6,
                      decoration: BoxDecoration(
                        color: isOnline ? Colors.greenAccent : Colors.redAccent,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      isOnline ? 'Online' : 'Offline',
                      style: const TextStyle(
                        fontSize: 11,
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
          const SizedBox(width: 8),
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Logout',
            onPressed: _handleLogout,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _handleSyncAndPull,
        color: primaryGreen,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Beautiful Header Card
              Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [primaryGreen, Color(0xFF073D24)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.only(
                    bottomLeft: Radius.circular(24),
                    bottomRight: Radius.circular(24),
                  ),
                ),
                padding: const EdgeInsets.fromLTRB(20, 10, 20, 24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 26,
                          backgroundColor: goldenAccent.withOpacity(0.2),
                          child: const Icon(Icons.person, color: goldenAccent, size: 30),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _activeUser?.name ?? 'Revenue Officer',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '@${_activeUser?.username ?? "officer"} • Field Agent',
                                style: TextStyle(
                                  fontSize: 13,
                                  color: Colors.white.withOpacity(0.7),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    // Action button inside header
                    Container(
                      width: double.infinity,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [goldenAccent, Color(0xFFAA843E)],
                          begin: Alignment.centerLeft,
                          end: Alignment.centerRight,
                        ),
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(
                            color: goldenAccent.withOpacity(0.3),
                            blurRadius: 8,
                            offset: const Offset(0, 4),
                          )
                        ],
                      ),
                      child: Material(
                        color: Colors.transparent,
                        child: InkWell(
                          onTap: _isSyncing ? null : _handleSyncAndPull,
                          borderRadius: BorderRadius.circular(14),
                          child: Padding(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                _isSyncing
                                    ? const SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2.5,
                                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                        ),
                                      )
                                    : const Icon(Icons.sync_outlined, color: Colors.white),
                                const SizedBox(width: 10),
                                Text(
                                  _isSyncing ? 'Synchronizing System...' : 'Sync & Pull Statuses',
                                  style: const TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.white,
                                    letterSpacing: 0.5,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Summary Section Header
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 20.0),
                child: Text(
                  'REGISTRATION STATISTICS',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey,
                    letterSpacing: 1.2,
                  ),
                ),
              ),

              const SizedBox(height: 12),

              // Interactive 2x2 Grid Section
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20.0),
                child: GridView.count(
                  crossAxisCount: 2,
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  mainAxisSpacing: 12,
                  crossAxisSpacing: 12,
                  childAspectRatio: 1.5,
                  children: [
                    _buildInteractiveCard(
                      filterValue: 'all',
                      label: 'All Registrations',
                      count: countAll,
                      color: primaryGreen,
                      icon: Icons.list_alt,
                    ),
                    _buildInteractiveCard(
                      filterValue: 'synced',
                      label: 'Synced with Server',
                      count: countSynced,
                      color: const Color(0xFF2E7D32),
                      icon: Icons.cloud_done,
                    ),
                    _buildInteractiveCard(
                      filterValue: 'pending_sync',
                      label: 'Pending Sync',
                      count: countPending,
                      color: const Color(0xFFE65100),
                      icon: Icons.cloud_upload_outlined,
                    ),
                    _buildInteractiveCard(
                      filterValue: 'invalid',
                      label: 'Corrections Required',
                      count: countInvalid,
                      color: const Color(0xFFC62828),
                      icon: Icons.warning_amber_rounded,
                    ),
                  ],
                ),
              ),

              // Quick Actions or Special Access Section
              if (_activeUser?.permissions.contains('view unpaid taxes') ?? false) ...[
                const SizedBox(height: 24),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 20.0),
                  child: Text(
                    'REVENUE ACTIONS',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: Colors.grey,
                      letterSpacing: 1.2,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20.0),
                  child: Card(
                    elevation: 2,
                    shadowColor: Colors.black.withOpacity(0.1),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                      side: BorderSide(color: goldenAccent.withOpacity(0.4), width: 1),
                    ),
                    child: Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        gradient: const LinearGradient(
                          colors: [Colors.white, Color(0xFFF9FBF9)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                      ),
                      child: InkWell(
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => const UnpaidTaxesScreen()),
                          );
                        },
                        borderRadius: BorderRadius.circular(16),
                        child: Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: primaryGreen.withOpacity(0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(
                                  Icons.account_balance_wallet_outlined,
                                  color: primaryGreen,
                                  size: 26,
                                ),
                              ),
                              const SizedBox(width: 16),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Unpaid Taxes Directory',
                                      style: TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF2C3E50),
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'View outstanding tax liabilities and sync offline records.',
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: Colors.grey[600],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const Icon(
                                Icons.chevron_right,
                                color: primaryGreen,
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ],

              const SizedBox(height: 24),

              // Filter label and list header
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20.0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      _getFilterLabel().toUpperCase(),
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: Colors.grey,
                        letterSpacing: 1.2,
                      ),
                    ),
                    Text(
                      '${filtered.length} entries',
                      style: const TextStyle(
                        fontSize: 12,
                        color: Colors.grey,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 12),

              // Establishment List View
              filtered.isEmpty
                  ? _buildEmptyState()
                  : ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: filtered.length,
                      padding: const EdgeInsets.symmetric(horizontal: 20.0),
                      itemBuilder: (context, index) {
                        final est = filtered[index];
                        return _buildEstablishmentRow(est);
                      },
                    ),
              const SizedBox(height: 80), // extra padding at bottom
            ],
          ),
        ),
      ),
      floatingActionButton: (_activeUser?.permissions.contains('create establishment') ?? false)
          ? FloatingActionButton.extended(
              backgroundColor: primaryGreen,
              foregroundColor: Colors.white,
              elevation: 4,
              onPressed: () async {
                final result = await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (context) => const RegistrationScreen()),
                );
                if (!mounted) return;
                if (result == true) _loadEstablishments();
              },
              icon: const Icon(Icons.add),
              label: const Text('Add Registration', style: TextStyle(fontWeight: FontWeight.bold)),
            )
          : null,
    );
  }

  String _getFilterLabel() {
    switch (_selectedFilter) {
      case 'synced':
        return 'Synced Registrations';
      case 'pending_sync':
        return 'Pending Sync';
      case 'invalid':
        return 'Corrections Required';
      default:
        return 'Recent Registrations';
    }
  }

  Widget _buildInteractiveCard({
    required String filterValue,
    required String label,
    required int count,
    required Color color,
    required IconData icon,
  }) {
    final isSelected = _selectedFilter == filterValue;
    const goldenAccent = Color(0xFFC5A059);

    return InkWell(
      onTap: () {
        setState(() {
          _selectedFilter = filterValue;
        });
      },
      borderRadius: BorderRadius.circular(16),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        curve: Curves.easeInOut,
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isSelected ? color : color.withOpacity(0.06),
          borderRadius: BorderRadius.circular(16),
          border: isSelected
              ? Border.all(color: goldenAccent, width: 3)
              : Border.all(color: color.withOpacity(0.15), width: 1.5),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: color.withOpacity(0.3),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  )
                ]
              : null,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Icon(
                  icon,
                  color: isSelected ? Colors.white : color,
                  size: 20,
                ),
                Text(
                  count.toString(),
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: isSelected ? Colors.white : color,
                  ),
                ),
              ],
            ),
            Text(
              label,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                color: isSelected ? Colors.white.withOpacity(0.9) : Colors.black87,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEstablishmentRow(Establishment est) {
    Color leftBarColor = const Color(0xFFE65100);
    String statusText = 'Pending';
    Color badgeColor = Colors.orange[50]!;
    Color badgeTextColor = Colors.orange[800]!;

    if (est.syncStatus == 'synced') {
      leftBarColor = const Color(0xFF2E7D32);
      statusText = 'Synced';
      badgeColor = Colors.green[50]!;
      badgeTextColor = Colors.green[800]!;
    } else if (est.syncStatus == 'invalid') {
      leftBarColor = const Color(0xFFC62828);
      statusText = 'Rejected';
      badgeColor = Colors.red[50]!;
      badgeTextColor = Colors.red[800]!;
    } else if (est.syncStatus == 'failed') {
      leftBarColor = const Color(0xFFC62828);
      statusText = 'Failed';
      badgeColor = Colors.red[50]!;
      badgeTextColor = Colors.red[800]!;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          )
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                width: 6,
                color: leftBarColor,
              ),
              Expanded(
                child: Material(
                  color: Colors.transparent,
                  child: InkWell(
                    onTap: () async {
                      await Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => DetailScreen(establishment: est),
                        ),
                      );
                      _loadEstablishments();
                    },
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  est.name,
                                  style: const TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF2C3E50),
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    const Icon(Icons.location_on_outlined, size: 13, color: Colors.grey),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        '${est.lga}, ${est.ward}',
                                        style: const TextStyle(
                                          fontSize: 12,
                                          color: Colors.grey,
                                        ),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ],
                                ),
                                if (est.streetAddress.isNotEmpty) ...[
                                  const SizedBox(height: 2),
                                  Text(
                                    est.streetAddress,
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: Colors.grey[600],
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ]
                              ],
                            ),
                          ),
                          const SizedBox(width: 12),
                          Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: badgeColor,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  statusText,
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                    color: badgeTextColor,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 6),
                              const Icon(Icons.chevron_right, color: Colors.grey, size: 18),
                            ],
                          )
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    IconData emptyIcon = Icons.inbox_outlined;
    String mainMsg = 'No registrations found';
    String subMsg = 'Tap the "+" button below to register a new establishment.';

    if (_selectedFilter == 'synced') {
      emptyIcon = Icons.cloud_off_outlined;
      mainMsg = 'No synced records';
      subMsg = 'Synchronized records will appear here after sync.';
    } else if (_selectedFilter == 'pending_sync') {
      emptyIcon = Icons.cloud_done_outlined;
      mainMsg = 'All caught up!';
      subMsg = 'There are no pending registrations waiting to sync.';
    } else if (_selectedFilter == 'invalid') {
      emptyIcon = Icons.assignment_turned_in_outlined;
      mainMsg = 'No corrections required';
      subMsg = 'Good job! All your synced entries are verified or pending review.';
    }

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 40.0, horizontal: 24.0),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(emptyIcon, size: 54, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            mainMsg,
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: Colors.grey[600],
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            subMsg,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[500],
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
