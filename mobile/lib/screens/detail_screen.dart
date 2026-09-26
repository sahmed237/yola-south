import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import '../models/establishment.dart';
import '../models/user.dart';
import '../services/database_helper.dart';
import '../services/network_service.dart';
import 'registration_screen.dart';

class DetailScreen extends StatefulWidget {
  final Establishment establishment;
  const DetailScreen({super.key, required this.establishment});

  @override
  State<DetailScreen> createState() => _DetailScreenState();
}

class _DetailScreenState extends State<DetailScreen> {
  final _dbHelper = DatabaseHelper();
  late Establishment _establishment;
  User? _activeUser;

  @override
  void initState() {
    super.initState();
    _establishment = widget.establishment;
    _loadActiveUser();
  }

  void _loadActiveUser() async {
    final user = await _dbHelper.getActiveUser();
    setState(() => _activeUser = user);
  }

  Future<void> _refreshData() async {
    if (_establishment.id != null) {
      final updated = await _dbHelper.getEstablishmentById(_establishment.id!);
      if (updated != null) {
        setState(() {
          _establishment = updated;
        });
      }
    }
  }

  Widget _buildDetailRow(String label, String value, {bool isMono = false}) {
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
            value,
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Colors.blueGrey,
              fontFamily: isMono ? 'monospace' : null,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionCard(String title, IconData icon, List<Widget> children) {
    return Card(
      elevation: 2,
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

  @override
  Widget build(BuildContext context) {
    final bool canEdit = _establishment.syncStatus != 'synced';
    const primaryGreen = Color(0xFF0A5C36);
    
    Color statusBg = Colors.orange[50]!;
    Color statusColor = Colors.orange[800]!;
    String statusText = 'Pending Synchronization';
    IconData statusIcon = Icons.cloud_upload_outlined;

    if (_establishment.syncStatus == 'synced') {
      statusBg = Colors.green[50]!;
      statusColor = Colors.green[800]!;
      statusText = 'Synced with Server';
      statusIcon = Icons.check_circle_outline;
    } else if (_establishment.syncStatus == 'invalid') {
      statusBg = Colors.red[50]!;
      statusColor = Colors.red[800]!;
      statusText = 'Corrections Required (Rejected)';
      statusIcon = Icons.warning_amber_rounded;
    } else if (_establishment.syncStatus == 'failed') {
      statusBg = Colors.red[50]!;
      statusColor = Colors.red[800]!;
      statusText = 'Sync Failed (Will Retry)';
      statusIcon = Icons.error_outline;
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Establishment Profile'),
        centerTitle: true,
        actions: [
          if (canEdit && (_activeUser?.permissions.contains('edit establishment') ?? false))
            IconButton(
              icon: const Icon(Icons.edit),
              tooltip: 'Edit Details',
              onPressed: () async {
                final result = await Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => RegistrationScreen(establishment: _establishment),
                  ),
                );
                if (result == true) {
                  _refreshData();
                }
              },
            ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Status Banner
            Container(
              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
              decoration: BoxDecoration(
                color: statusBg,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: statusColor.withValues(alpha: 0.3)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(statusIcon, color: statusColor),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          statusText,
                          style: TextStyle(
                            color: statusColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (_establishment.syncStatus == 'invalid' &&
                      _establishment.rejectionRemarks != null &&
                      _establishment.rejectionRemarks!.isNotEmpty) ...[
                    const SizedBox(height: 10),
                    const Divider(color: Colors.redAccent, height: 1),
                    const SizedBox(height: 10),
                    const Text(
                      'CORRECTION REMARKS:',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: Colors.red,
                        letterSpacing: 0.8,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _establishment.rejectionRemarks!,
                      style: const TextStyle(
                        fontSize: 13,
                        color: Colors.black87,
                        fontStyle: FontStyle.italic,
                      ),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Images Carousel/Row
            if (_establishment.images.isNotEmpty) ...[
              const Text(
                'ATTACHED IMAGES',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey,
                  letterSpacing: 1.1,
                ),
              ),
              const SizedBox(height: 8),
              SizedBox(
                height: 120,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  itemCount: _establishment.images.length,
                  itemBuilder: (context, idx) {
                    final imgPath = _establishment.images[idx];
                    final isLocalFile = !imgPath.startsWith('http') && !imgPath.startsWith('assets/');
                    return Padding(
                      padding: const EdgeInsets.only(right: 8.0),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: SizedBox(
                          width: 160,
                          height: 120,
                          child: isLocalFile
                              ? Image.file(
                                  File(imgPath),
                                  fit: BoxFit.cover,
                                  errorBuilder: (c, e, s) => Container(
                                    color: Colors.grey[200],
                                    child: const Icon(Icons.broken_image, color: Colors.grey),
                                  ),
                                )
                              : Image.network(
                                  imgPath,
                                  fit: BoxFit.cover,
                                  errorBuilder: (c, e, s) => Container(
                                    color: Colors.grey[200],
                                    child: const Icon(Icons.broken_image, color: Colors.grey),
                                  ),
                                ),
                        ),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Section 1: General Details
            _buildSectionCard(
              'General Details',
              Icons.storefront,
              [
                _buildDetailRow('Establishment Name', _establishment.name),
                _buildDetailRow('Establishment Type', _establishment.type),
                _buildDetailRow('Establishment Size', _establishment.size),
                _buildDetailRow('Base Year', _establishment.baseYear.toString()),
              ],
            ),

            // Section 2: Location & Address
            _buildSectionCard(
              'Location & Address',
              Icons.location_on,
              [
                _buildDetailRow('LGA', _establishment.lga),
                _buildDetailRow('Ward', _establishment.ward),
                _buildDetailRow('Street Address', _establishment.streetAddress),
                _buildDetailRow('House/Suite Number', _establishment.houseNumber),
                _buildDetailRow('City', _establishment.city),
                _buildDetailRow('Postal Code', _establishment.postalCode),
                _buildDetailRow('Inside Metropolis Limit', _establishment.insideMetropolis ? 'Yes' : 'No'),
                _buildDetailRow(
                  'GPS Coordinates',
                  _establishment.lat != null && _establishment.lng != null
                      ? 'Lat: ${_establishment.lat!.toStringAsFixed(8)}\nLng: ${_establishment.lng!.toStringAsFixed(8)}'
                      : 'Not captured',
                  isMono: true,
                ),
                if (_establishment.lat != null && _establishment.lng != null && NetworkService().isOnline.value) ...[
                  const SizedBox(height: 12),
                  Container(
                    height: 200,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey[300]!),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: FlutterMap(
                        options: MapOptions(
                          initialCenter: LatLng(_establishment.lat!, _establishment.lng!),
                          initialZoom: 15.0,
                        ),
                        children: [
                          TileLayer(
                            urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                            userAgentPackageName: 'com.urcs.revenue_mobile',
                          ),
                          MarkerLayer(
                            markers: [
                              Marker(
                                point: LatLng(_establishment.lat!, _establishment.lng!),
                                width: 40,
                                height: 40,
                                child: const Icon(
                                  Icons.location_on,
                                  color: Colors.red,
                                  size: 40,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ],
            ),

            // Section 3: Owner Details
            _buildSectionCard(
              'Owner Profile',
              Icons.person,
              [
                _buildDetailRow('Owner Name', _establishment.ownerName ?? 'Not provided'),
                _buildDetailRow('Owner Gender/Type', _establishment.ownerGender ?? 'Not provided'),
                _buildDetailRow('Owner Phone', _establishment.ownerPhone ?? 'Not provided'),
                _buildDetailRow('Owner Email', _establishment.ownerEmail ?? 'Not provided'),
                _buildDetailRow('Owner NIN', _establishment.ownerNin ?? 'Not provided'),
              ],
            ),

            // Section 4: Occupant Details
            _buildSectionCard(
              'Occupant Profile',
              Icons.person_pin,
              [
                _buildDetailRow('Occupant Name', _establishment.occupantName ?? 'Not provided'),
                _buildDetailRow('Occupant Phone', _establishment.occupantPhone ?? 'Not provided'),
              ],
            ),
            
            // Bottom Edit Action for failed or pending entries
            if (canEdit && (_activeUser?.permissions.contains('edit establishment') ?? false)) ...[
              const SizedBox(height: 8),
              ElevatedButton.icon(
                onPressed: () async {
                  final result = await Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => RegistrationScreen(establishment: _establishment),
                    ),
                  );
                  if (result == true) {
                    _refreshData();
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryGreen,
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                icon: const Icon(Icons.edit),
                label: const Text('Edit Details Offline', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
              const SizedBox(height: 16),
            ]
          ],
        ),
      ),
    );
  }
}
