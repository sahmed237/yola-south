import 'package:flutter/material.dart';
import '../services/database_helper.dart';
import '../services/sync_service.dart';
import '../services/network_service.dart';

class UnpaidTaxesScreen extends StatefulWidget {
  const UnpaidTaxesScreen({super.key});

  @override
  State<UnpaidTaxesScreen> createState() => _UnpaidTaxesScreenState();
}

class _UnpaidTaxesScreenState extends State<UnpaidTaxesScreen> {
  final _dbHelper = DatabaseHelper();
  final _syncService = SyncService();
  
  List<Map<String, dynamic>> _unpaidList = [];
  List<Map<String, dynamic>> _filteredList = [];
  bool _isLoading = false;
  final _searchController = TextEditingController();
  bool _hasAutoPulled = false;

  @override
  void initState() {
    super.initState();
    _loadUnpaidTaxes();
    _searchController.addListener(_onSearchChanged);
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  void _loadUnpaidTaxes() async {
    setState(() => _isLoading = true);
    try {
      final list = await _dbHelper.getUnpaidEstablishments();
      setState(() {
        _unpaidList = list;
        _filteredList = list;
      });
      if (list.isEmpty && NetworkService().isOnline.value && !_hasAutoPulled) {
        _hasAutoPulled = true;
        _handlePullRefresh();
      }
    } catch (e) {
      debugPrint('Error loading unpaid taxes: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _onSearchChanged() {
    final query = _searchController.text.toLowerCase().trim();
    if (query.isEmpty) {
      setState(() {
        _filteredList = _unpaidList;
      });
    } else {
      setState(() {
        _filteredList = _unpaidList.where((item) {
          final name = (item['name'] ?? '').toString().toLowerCase();
          final lga = (item['lga'] ?? '').toString().toLowerCase();
          final ward = (item['ward'] ?? '').toString().toLowerCase();
          final owner = (item['owner_name'] ?? '').toString().toLowerCase();
          return name.contains(query) || lga.contains(query) || ward.contains(query) || owner.contains(query);
        }).toList();
      });
    }
  }

  Future<void> _handlePullRefresh() async {
    if (!NetworkService().isOnline.value) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Cannot sync: Device is offline.'),
          backgroundColor: Colors.amber,
        ),
      );
      return;
    }

    try {
      await _syncService.pullUnpaidTaxes();
      _loadUnpaidTaxes();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Unpaid taxes synchronized successfully.'),
          backgroundColor: Color(0xFF0A5C36),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Synchronization failed: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  String _formatCurrency(double amount) {
    final String amountStr = amount.toStringAsFixed(2);
    final List<String> parts = amountStr.split('.');
    final RegExp reg = RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))');
    final String formattedInt = parts[0].replaceAllMapped(reg, (Match match) => '${match[1]},');
    return '₦$formattedInt.${parts[1]}';
  }

  void _showDetailsModal(Map<String, dynamic> item) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        final double outstanding = (item['outstanding_amount'] as num?)?.toDouble() ?? 0.0;
        final double due = (item['total_due'] as num?)?.toDouble() ?? 0.0;
        final double paid = (item['total_paid'] as num?)?.toDouble() ?? 0.0;

        return DraggableScrollableSheet(
          initialChildSize: 0.85,
          minChildSize: 0.5,
          maxChildSize: 0.95,
          builder: (context, scrollController) {
            return Container(
              decoration: const BoxDecoration(
                color: Color(0xFFF4F6F8),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(24),
                  topRight: Radius.circular(24),
                ),
              ),
              child: Column(
                children: [
                  // Drag Handle & Header
                  Container(
                    margin: const EdgeInsets.only(top: 12, bottom: 8),
                    width: 40,
                    height: 5,
                    decoration: BoxDecoration(
                      color: Colors.grey[400],
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Tax Liability Details',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF2C3E50),
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                  ),
                  const Divider(height: 1),
                  Expanded(
                    child: ListView(
                      controller: scrollController,
                      padding: const EdgeInsets.all(20),
                      children: [
                        // Establishment General Info Card
                        _buildSectionCard(
                          'Establishment Profile',
                          Icons.business,
                          [
                            _buildDetailRow('Name', item['name'] ?? ''),
                            _buildDetailRow('Type', item['type'] ?? ''),
                            _buildDetailRow('Size', item['size'] ?? ''),
                            _buildDetailRow('Location', '${item['lga']}, ${item['ward']}'),
                            _buildDetailRow('Street Address', item['street_address'] ?? 'Not provided'),
                            _buildDetailRow('House Number', item['house_number'] ?? 'Not provided'),
                            _buildDetailRow('Base Year', (item['base_year'] ?? '').toString()),
                          ],
                        ),
                        
                        // Liability Summary Card
                        _buildSectionCard(
                          'Tax Account Statement',
                          Icons.account_balance_wallet,
                          [
                            _buildFinancialRow('Total Due', _formatCurrency(due), color: Colors.blueGrey),
                            _buildFinancialRow('Total Paid', _formatCurrency(paid), color: Colors.green[800]!),
                            const Divider(height: 20),
                            _buildFinancialRow(
                              'Outstanding Balance',
                              _formatCurrency(outstanding),
                              color: const Color(0xFFC62828),
                              isBold: true,
                            ),
                          ],
                        ),

                        // Owner Card
                        _buildSectionCard(
                          'Owner Profile',
                          Icons.person,
                          [
                            _buildDetailRow('Name', item['owner_name'] ?? 'Not provided'),
                            _buildDetailRow('Gender/Type', item['owner_gender'] ?? 'Not provided'),
                            _buildDetailRow('Phone', item['owner_phone'] ?? 'Not provided'),
                            _buildDetailRow('Email', item['owner_email'] ?? 'Not provided'),
                            _buildDetailRow('NIN', item['owner_nin'] ?? 'Not provided'),
                          ],
                        ),

                        // Occupant Card
                        _buildSectionCard(
                          'Occupant Profile',
                          Icons.person_pin,
                          [
                            _buildDetailRow('Name', item['occupant_name'] ?? 'Not provided'),
                            _buildDetailRow('Phone', item['occupant_phone'] ?? 'Not provided'),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildSectionCard(String title, IconData icon, List<Widget> children) {
    return Card(
      elevation: 1.5,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, color: const Color(0xFF0A5C36), size: 20),
                const SizedBox(width: 8),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF0A5C36),
                  ),
                ),
              ],
            ),
            const Divider(height: 20, thickness: 1),
            ...children,
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label.toUpperCase(),
            style: const TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.bold,
              color: Colors.grey,
              letterSpacing: 1.1,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            value.isEmpty ? 'Not provided' : value,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Colors.blueGrey,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFinancialRow(String label, String value, {required Color color, bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 14,
              fontWeight: isBold ? FontWeight.bold : FontWeight.w500,
              color: isBold ? Colors.black87 : Colors.grey[700],
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    const primaryGreen = Color(0xFF0A5C36);

    return Scaffold(
      backgroundColor: const Color(0xFFF4F6F8),
      appBar: AppBar(
        title: const Text('Unpaid Taxes Directory'),
        backgroundColor: primaryGreen,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Search & Connection Status Header
          Container(
            color: primaryGreen,
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
            child: Column(
              children: [
                // Search Bar
                TextField(
                  controller: _searchController,
                  style: const TextStyle(color: Colors.black87),
                  decoration: InputDecoration(
                    hintText: 'Search by name, LGA, ward or owner...',
                    hintStyle: TextStyle(color: Colors.grey[500]),
                    prefixIcon: const Icon(Icons.search, color: primaryGreen),
                    fillColor: Colors.white,
                    filled: true,
                    contentPadding: const EdgeInsets.symmetric(vertical: 0),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                // Pull info banner
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    ValueListenableBuilder<bool>(
                      valueListenable: NetworkService().isOnline,
                      builder: (context, isOnline, child) {
                        return Row(
                          children: [
                            Container(
                              width: 8,
                              height: 8,
                              decoration: BoxDecoration(
                                color: isOnline ? Colors.greenAccent : Colors.redAccent,
                                shape: BoxShape.circle,
                              ),
                            ),
                            const SizedBox(width: 6),
                            Text(
                              isOnline ? 'Online (Pull active)' : 'Offline mode',
                              style: const TextStyle(color: Colors.white70, fontSize: 12),
                            ),
                          ],
                        );
                      },
                    ),
                    Text(
                      '${_filteredList.length} liabilities found',
                      style: const TextStyle(color: Colors.white70, fontSize: 12),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Main List or Empty State
          Expanded(
            child: RefreshIndicator(
              onRefresh: _handlePullRefresh,
              color: primaryGreen,
              child: _isLoading && _unpaidList.isEmpty
                  ? const Center(child: CircularProgressIndicator(color: primaryGreen))
                  : _filteredList.isEmpty
                      ? _buildEmptyState()
                      : ListView.builder(
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: const EdgeInsets.all(16),
                          itemCount: _filteredList.length,
                          itemBuilder: (context, index) {
                            final item = _filteredList[index];
                            final double outstanding = (item['outstanding_amount'] as num?)?.toDouble() ?? 0.0;
                            
                            return Card(
                              elevation: 2,
                              margin: const EdgeInsets.only(bottom: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              child: InkWell(
                                onTap: () => _showDetailsModal(item),
                                borderRadius: BorderRadius.circular(16),
                                child: Padding(
                                  padding: const EdgeInsets.all(16.0),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      // Icon/Outstanding Balance Badge
                                      Container(
                                        padding: const EdgeInsets.all(12),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFC62828).withOpacity(0.08),
                                          shape: BoxShape.circle,
                                        ),
                                        child: const Icon(
                                          Icons.account_balance_wallet_outlined,
                                          color: Color(0xFFC62828),
                                          size: 24,
                                        ),
                                      ),
                                      const SizedBox(width: 14),
                                      
                                      // Details
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              item['name'] ?? '',
                                              style: const TextStyle(
                                                fontSize: 15,
                                                fontWeight: FontWeight.bold,
                                                color: Color(0xFF2C3E50),
                                              ),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                            const SizedBox(height: 4),
                                            Text(
                                              '${item['lga']}, ${item['ward']}',
                                              style: TextStyle(
                                                fontSize: 12,
                                                color: Colors.grey[600],
                                              ),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                            if (item['owner_name'] != null) ...[
                                              const SizedBox(height: 4),
                                              Text(
                                                'Owner: ${item['owner_name']}',
                                                style: TextStyle(
                                                  fontSize: 11,
                                                  color: Colors.grey[500],
                                                  fontStyle: FontStyle.italic,
                                                ),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ],
                                          ],
                                        ),
                                      ),
                                      const SizedBox(width: 8),

                                      // Outstanding Amount
                                      Column(
                                        crossAxisAlignment: CrossAxisAlignment.end,
                                        children: [
                                          Text(
                                            _formatCurrency(outstanding),
                                            style: const TextStyle(
                                              fontSize: 14,
                                              fontWeight: FontWeight.bold,
                                              color: Color(0xFFC62828),
                                            ),
                                          ),
                                          const SizedBox(height: 6),
                                          const Icon(Icons.arrow_forward_ios, size: 12, color: Colors.grey),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 80, horizontal: 24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.check_circle_outline, size: 64, color: Colors.green[400]),
            const SizedBox(height: 16),
            const Text(
              'No unpaid taxes found',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2C3E50),
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Pull down to refresh and sync offline records with the server.',
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey[600],
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
