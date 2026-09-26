import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';
import '../models/establishment.dart';
import '../services/database_helper.dart';
import '../services/network_service.dart';

class RegistrationScreen extends StatefulWidget {
  final Establishment? establishment;
  const RegistrationScreen({super.key, this.establishment});

  @override
  State<RegistrationScreen> createState() => _RegistrationScreenState();
}

class _RegistrationScreenState extends State<RegistrationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _dbHelper = DatabaseHelper();
  final ImagePicker _picker = ImagePicker();

  // Controllers for text inputs
  late final TextEditingController _nameController;
  late final TextEditingController _baseYearController;
  late final TextEditingController _streetAddressController;
  late final TextEditingController _houseNumberController;
  late final TextEditingController _postalCodeController;
  late final TextEditingController _cityController;
  late final TextEditingController _ownerNameController;
  late final TextEditingController _ownerPhoneController;
  late final TextEditingController _ownerEmailController;
  late final TextEditingController _ownerNinController;
  late final TextEditingController _occupantNameController;
  late final TextEditingController _occupantPhoneController;

  String type = '';
  String size = '';
  String lga = '';
  String ward = '';
  bool insideMetropolis = false;
  String? ownerGender;
  double? lat;
  double? lng;
  int baseYear = DateTime.now().year;

  bool _isGettingLocation = false;
  bool _isLoadingMetadata = true;

  List<Map<String, dynamic>> _lgas = [];
  List<Map<String, dynamic>> _wards = [];
  List<String> _types = [];
  List<String> _sizes = [];
  List<String> _selectedImages = [];

  final List<String> _genders = [
    'Male',
    'Female',
    'Corporate'
  ];

  @override
  void initState() {
    super.initState();
    
    _nameController = TextEditingController();
    _baseYearController = TextEditingController(text: baseYear.toString());
    _streetAddressController = TextEditingController();
    _houseNumberController = TextEditingController();
    _postalCodeController = TextEditingController();
    _cityController = TextEditingController();
    _ownerNameController = TextEditingController();
    _ownerPhoneController = TextEditingController();
    _ownerEmailController = TextEditingController();
    _ownerNinController = TextEditingController();
    _occupantNameController = TextEditingController();
    _occupantPhoneController = TextEditingController();

    // Listen to owner fields to update validation dynamically
    _ownerNameController.addListener(_onOwnerFieldChanged);
    _ownerPhoneController.addListener(_onOwnerFieldChanged);
    _ownerEmailController.addListener(_onOwnerFieldChanged);
    _ownerNinController.addListener(_onOwnerFieldChanged);

    _loadMetadata();
  }

  void _onOwnerFieldChanged() {
    setState(() {});
  }

  bool _isOwnerTouched() {
    return _ownerNameController.text.trim().isNotEmpty ||
        _ownerPhoneController.text.trim().isNotEmpty ||
        _ownerEmailController.text.trim().isNotEmpty ||
        _ownerNinController.text.trim().isNotEmpty ||
        (ownerGender != null && ownerGender!.isNotEmpty);
  }

  @override
  void dispose() {
    _ownerNameController.removeListener(_onOwnerFieldChanged);
    _ownerPhoneController.removeListener(_onOwnerFieldChanged);
    _ownerEmailController.removeListener(_onOwnerFieldChanged);
    _ownerNinController.removeListener(_onOwnerFieldChanged);

    _nameController.dispose();
    _baseYearController.dispose();
    _streetAddressController.dispose();
    _houseNumberController.dispose();
    _postalCodeController.dispose();
    _cityController.dispose();
    _ownerNameController.dispose();
    _ownerPhoneController.dispose();
    _ownerEmailController.dispose();
    _ownerNinController.dispose();
    _occupantNameController.dispose();
    _occupantPhoneController.dispose();
    super.dispose();
  }

  Future<void> _loadMetadata() async {
    try {
      final lgas = await _dbHelper.getLgas();
      final types = await _dbHelper.getEstablishmentTypes();
      final sizes = await _dbHelper.getEstablishmentSizes();

      setState(() {
        _lgas = lgas;
        _types = types;
        _sizes = sizes;

        if (widget.establishment != null) {
          final est = widget.establishment!;
          _nameController.text = est.name;
          type = est.type;
          size = est.size;
          lga = est.lga;
          insideMetropolis = est.insideMetropolis;
          _streetAddressController.text = est.streetAddress;
          _houseNumberController.text = est.houseNumber;
          _cityController.text = est.city;
          _postalCodeController.text = est.postalCode;
          _ownerNameController.text = est.ownerName ?? '';
          ownerGender = est.ownerGender;
          _ownerPhoneController.text = est.ownerPhone ?? '';
          _ownerEmailController.text = est.ownerEmail ?? '';
          _ownerNinController.text = est.ownerNin ?? '';
          _occupantNameController.text = est.occupantName ?? '';
          _occupantPhoneController.text = est.occupantPhone ?? '';
          lat = est.lat;
          lng = est.lng;
          baseYear = est.baseYear;
          _baseYearController.text = baseYear.toString();
          _selectedImages = List<String>.from(est.images);
          _loadWardsForLga(lga);
        } else {
          type = '';
          size = '';
          lga = '';
          ward = '';
          _isLoadingMetadata = false;
        }
      });
    } catch (e) {
      setState(() {
        _isLoadingMetadata = false;
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Failed to load form metadata: $e'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  Future<void> _loadWardsForLga(String lgaName) async {
    if (lgaName.isEmpty) {
      setState(() {
        _wards = [];
        ward = '';
        _isLoadingMetadata = false;
      });
      return;
    }
    setState(() {
      _isLoadingMetadata = true;
    });
    try {
      final wards = await _dbHelper.getWardsForLga(lgaName);
      setState(() {
        _wards = wards;
        if (widget.establishment != null && widget.establishment!.lga == lgaName) {
          ward = widget.establishment!.ward;
        } else {
          ward = '';
        }
        _isLoadingMetadata = false;
      });
    } catch (e) {
      setState(() {
        _isLoadingMetadata = false;
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Failed to load wards: $e'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  Future<void> _pickImage(ImageSource source) async {
    if (_selectedImages.length >= 3) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('You can only upload a maximum of 3 images.'),
          backgroundColor: Colors.redAccent,
        ),
      );
      return;
    }
    try {
      final XFile? image = await _picker.pickImage(
        source: source,
        imageQuality: 80,
      );
      if (image != null) {
        setState(() {
          _selectedImages.add(image.path);
        });
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Failed to capture image: $e'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  void _removeImage(int index) {
    setState(() {
      _selectedImages.removeAt(index);
    });
  }

  Future<void> _getLocation() async {
    setState(() => _isGettingLocation = true);
    try {
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        throw 'Location services are disabled.';
      }

      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          throw 'Location permissions are denied';
        }
      }
      
      if (permission == LocationPermission.deniedForever) {
        throw 'Location permissions are permanently denied, we cannot request permissions.';
      }

      Position position = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high);
      if (!mounted) return;
      setState(() {
        lat = position.latitude;
        lng = position.longitude;
      });
      ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('GPS Coordinates Captured: $lat, $lng'),
            backgroundColor: const Color(0xFF0A5C36),
          ));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Could not capture coordinates: $e'),
            backgroundColor: Colors.redAccent,
          ));
    } finally {
      if (mounted) {
        setState(() => _isGettingLocation = false);
      }
    }
  }

  void _saveEstablishment() async {
    if (_formKey.currentState!.validate()) {
      if (lat == null || lng == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Please capture GPS coordinates before saving.'),
            backgroundColor: Colors.redAccent,
          ),
        );
        return;
      }

      if (_selectedImages.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Please capture or upload at least 1 image of the establishment.'),
            backgroundColor: Colors.redAccent,
          ),
        );
        return;
      }

      if (_selectedImages.length > 3) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Maximum of 3 images allowed.'),
            backgroundColor: Colors.redAccent,
          ),
        );
        return;
      }

      _formKey.currentState!.save();
      
      Establishment establishment = Establishment(
        id: widget.establishment?.id,
        name: _nameController.text.trim(),
        type: type,
        size: size,
        lga: lga,
        ward: ward,
        lat: lat,
        lng: lng,
        occupantName: _occupantNameController.text.trim().isNotEmpty ? _occupantNameController.text.trim() : null,
        occupantPhone: _occupantPhoneController.text.trim().isNotEmpty ? _occupantPhoneController.text.trim() : null,
        insideMetropolis: insideMetropolis,
        streetAddress: _streetAddressController.text.trim(),
        houseNumber: _houseNumberController.text.trim(),
        city: _cityController.text.trim(),
        postalCode: _postalCodeController.text.trim(),
        ownerName: _ownerNameController.text.trim().isNotEmpty ? _ownerNameController.text.trim() : null,
        ownerGender: ownerGender != null && ownerGender!.isNotEmpty ? ownerGender : null,
        ownerPhone: _ownerPhoneController.text.trim().isNotEmpty ? _ownerPhoneController.text.trim() : null,
        ownerEmail: _ownerEmailController.text.trim().isNotEmpty ? _ownerEmailController.text.trim() : null,
        ownerNin: _ownerNinController.text.trim().isNotEmpty ? _ownerNinController.text.trim() : null,
        baseYear: baseYear,
        images: _selectedImages,
        syncStatus: widget.establishment != null ? widget.establishment!.syncStatus : 'pending_sync',
      );

      if (widget.establishment != null) {
        if (establishment.syncStatus == 'failed' || establishment.syncStatus == 'invalid') {
          establishment.syncStatus = 'pending_sync';
        }
        await _dbHelper.updateEstablishment(establishment);
      } else {
        await _dbHelper.insertEstablishment(establishment);
      }

      if (!mounted) return;
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(widget.establishment != null
              ? 'Establishment details updated successfully!'
              : 'Establishment registered successfully offline!'),
          backgroundColor: const Color(0xFF0A5C36),
        )
      );
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please resolve the errors in the form before saving.'),
          backgroundColor: Colors.redAccent,
        )
      );
    }
  }

  Widget _buildSectionHeader(String title, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12.0),
      child: Row(
        children: [
          Icon(icon, color: const Color(0xFF0A5C36), size: 24),
          const SizedBox(width: 8),
          Text(
            title,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF0A5C36),
            ),
          ),
        ],
      ),
    );
  }

  InputDecoration _buildInputDecoration(String labelText, {IconData? prefixIcon}) {
    return InputDecoration(
      labelText: labelText,
      prefixIcon: prefixIcon != null ? Icon(prefixIcon, color: const Color(0xFF0A5C36)) : null,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Colors.grey),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFF0A5C36), width: 2),
      ),
      filled: true,
      fillColor: Colors.grey[50],
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
    );
  }

  @override
  Widget build(BuildContext context) {
    final bool isEdit = widget.establishment != null;
    final bool isOwnerTouched = _isOwnerTouched();

    return Scaffold(
      appBar: AppBar(
        title: Text(isEdit ? 'Edit Establishment' : 'Register New Establishment'),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (_isLoadingMetadata)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 8.0),
                  child: LinearProgressIndicator(
                    color: Color(0xFF0A5C36),
                    backgroundColor: Color(0xFFE8F2EC),
                  ),
                ),
              if (_lgas.isEmpty && !_isLoadingMetadata)
                Container(
                  margin: const EdgeInsets.only(bottom: 16),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.amber[50],
                    border: Border.all(color: Colors.amber[200]!),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.warning_amber_rounded, color: Colors.amber[800]),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'No State LGAs or categories found. Please log in online first to cache state configuration data.',
                          style: TextStyle(color: Colors.amber[900], fontSize: 13, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                ),
              // 1. General Details Card
              Card(
                elevation: 3,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                margin: const EdgeInsets.only(bottom: 16),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildSectionHeader('General Details', Icons.storefront),
                      TextFormField(
                        controller: _nameController,
                        decoration: _buildInputDecoration('Establishment Name *', prefixIcon: Icons.store),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Establishment name is required' : null,
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        isExpanded: true,
                        value: _types.contains(type) ? type : null,
                        hint: const Text('Select Category'),
                        items: _types
                            .map((t) => DropdownMenuItem(value: t, child: Text(t)))
                            .toList(),
                        onChanged: (v) => setState(() => type = v!),
                        decoration: _buildInputDecoration('Establishment Type *', prefixIcon: Icons.category),
                        validator: (v) => (v == null || v.isEmpty) ? 'Establishment type is required' : null,
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        isExpanded: true,
                        value: _sizes.contains(size) ? size : null,
                        hint: const Text('Select Size'),
                        items: _sizes
                            .map((s) => DropdownMenuItem(value: s, child: Text(s)))
                            .toList(),
                        onChanged: (v) => setState(() => size = v!),
                        decoration: _buildInputDecoration('Establishment Size *', prefixIcon: Icons.photo_size_select_small),
                        validator: (v) => (v == null || v.isEmpty) ? 'Establishment size is required' : null,
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _baseYearController,
                        keyboardType: TextInputType.number,
                        decoration: _buildInputDecoration('Base Year *', prefixIcon: Icons.calendar_today),
                        validator: (v) {
                          if (v == null || v.trim().isEmpty) {
                            return 'Base year is required';
                          }
                          final parsed = int.tryParse(v);
                          if (parsed == null || parsed < 1900 || parsed > 2100) {
                            return 'Enter a valid year (e.g. 2026)';
                          }
                          return null;
                        },
                        onSaved: (v) => baseYear = int.parse(v!.trim()),
                      ),
                    ],
                  ),
                ),
              ),

              // 2. Location & Address Card
              Card(
                elevation: 3,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                margin: const EdgeInsets.only(bottom: 16),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildSectionHeader('Location & Address', Icons.location_on),
                      Row(
                        children: [
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              isExpanded: true,
                              value: _lgas.any((l) => l['name'] == lga) ? lga : null,
                              hint: const Text('Select LGA'),
                              items: _lgas
                                  .map((l) => DropdownMenuItem(
                                        value: l['name'] as String,
                                        child: Text(l['name'] as String),
                                      ))
                                  .toList(),
                              onChanged: (v) {
                                if (v != null) {
                                  setState(() {
                                    lga = v;
                                    ward = '';
                                  });
                                  _loadWardsForLga(v);
                                }
                              },
                              decoration: _buildInputDecoration('LGA *'),
                              validator: (v) => (v == null || v.isEmpty) ? 'LGA is required' : null,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              isExpanded: true,
                              value: _wards.any((w) => w['name'] == ward) ? ward : null,
                              hint: const Text('Select Ward'),
                              items: _wards
                                  .map((w) => DropdownMenuItem(
                                        value: w['name'] as String,
                                        child: Text(w['name'] as String),
                                      ))
                                  .toList(),
                              onChanged: (v) {
                                if (v != null) {
                                  setState(() {
                                    ward = v;
                                  });
                                }
                              },
                              decoration: _buildInputDecoration('Ward *'),
                              validator: (v) => (v == null || v.isEmpty) ? 'Ward is required' : null,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _streetAddressController,
                        decoration: _buildInputDecoration('Street Address *', prefixIcon: Icons.add_road),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Street address is required' : null,
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _houseNumberController,
                              decoration: _buildInputDecoration('House Number *'),
                              validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: TextFormField(
                              controller: _postalCodeController,
                              decoration: _buildInputDecoration('Postal Code *'),
                              validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _cityController,
                        decoration: _buildInputDecoration('City *', prefixIcon: Icons.location_city),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'City is required' : null,
                      ),
                      const SizedBox(height: 12),
                      SwitchListTile(
                        title: const Text(
                          'Inside Metropolis *',
                          style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF0A5C36)),
                        ),
                        subtitle: const Text('Check if location is within metropolis boundaries'),
                        value: insideMetropolis,
                        onChanged: (v) => setState(() => insideMetropolis = v),
                      ),
                      const SizedBox(height: 12),
                      
                      // GPS captures
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.grey[100],
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.grey[300]!),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'GPS Coordinates *',
                                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    lat == null ? 'Not Captured' : 'Lat: ${lat!.toStringAsFixed(8)}\nLng: ${lng!.toStringAsFixed(8)}',
                                    style: TextStyle(
                                      color: lat == null ? Colors.red : Colors.green[800],
                                      fontFamily: 'monospace',
                                      fontSize: 13,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            ElevatedButton.icon(
                              onPressed: _isGettingLocation ? null : _getLocation,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFFC5A059),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              ),
                              icon: _isGettingLocation 
                                ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                : const Icon(Icons.gps_fixed, size: 16),
                              label: Text(_isGettingLocation ? 'Reading...' : 'Capture GPS'),
                            ),
                          ],
                        ),
                      ),
                      if (lat != null && lng != null && NetworkService().isOnline.value) ...[
                        const SizedBox(height: 12),
                        Container(
                          height: 220,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.grey[300]!),
                          ),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: FlutterMap(
                              options: MapOptions(
                                initialCenter: LatLng(lat!, lng!),
                                initialZoom: 15.0,
                                onTap: (tapPosition, point) {
                                  setState(() {
                                    lat = point.latitude;
                                    lng = point.longitude;
                                  });
                                },
                              ),
                              children: [
                                TileLayer(
                                  urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                                  userAgentPackageName: 'com.urcs.revenue_mobile',
                                ),
                                MarkerLayer(
                                  markers: [
                                    Marker(
                                      point: LatLng(lat!, lng!),
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
                        const SizedBox(height: 4),
                        const Text(
                          'Tap anywhere on the map to adjust coordinates.',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey,
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),

              // 3. Establishment Images Card
              Card(
                elevation: 3,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                margin: const EdgeInsets.only(bottom: 16),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildSectionHeader('Establishment Images *', Icons.camera_alt),
                      const Text(
                        'REQUIRED: 1 - 3 PHOTOS',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: Colors.grey,
                          letterSpacing: 1.1,
                        ),
                      ),
                      const SizedBox(height: 12),
                      
                      // Image previews row/grid
                      if (_selectedImages.isNotEmpty) ...[
                        GridView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 3,
                            crossAxisSpacing: 8,
                            mainAxisSpacing: 8,
                          ),
                          itemCount: _selectedImages.length,
                          itemBuilder: (context, idx) {
                            final imagePath = _selectedImages[idx];
                            final isLocalFile = !imagePath.startsWith('http') && !imagePath.startsWith('assets/');
                            return Stack(
                              children: [
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: isLocalFile
                                      ? Image.file(
                                          File(imagePath),
                                          width: double.infinity,
                                          height: double.infinity,
                                          fit: BoxFit.cover,
                                        )
                                      : Image.network(
                                          imagePath,
                                          width: double.infinity,
                                          height: double.infinity,
                                          fit: BoxFit.cover,
                                        ),
                                ),
                                Positioned(
                                  top: 4,
                                  right: 4,
                                  child: GestureDetector(
                                    onTap: () => _removeImage(idx),
                                    child: Container(
                                      padding: const EdgeInsets.all(4),
                                      decoration: const BoxDecoration(
                                        color: Colors.red,
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(
                                        Icons.close,
                                        color: Colors.white,
                                        size: 14,
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            );
                          },
                        ),
                        const SizedBox(height: 16),
                      ],
                      
                      // Gallery / Camera buttons
                      if (_selectedImages.length < 3)
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: () => _pickImage(ImageSource.gallery),
                                style: OutlinedButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  side: const BorderSide(color: Color(0xFF0A5C36)),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                icon: const Icon(Icons.photo_library, color: Color(0xFF0A5C36)),
                                label: const Text(
                                  'Gallery',
                                  style: TextStyle(color: Color(0xFF0A5C36), fontWeight: FontWeight.bold),
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: ElevatedButton.icon(
                                onPressed: () => _pickImage(ImageSource.camera),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF0A5C36),
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                icon: const Icon(Icons.camera_alt),
                                label: const Text(
                                  'Camera',
                                  style: TextStyle(fontWeight: FontWeight.bold),
                                ),
                              ),
                            ),
                          ],
                        ),
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.amber[50],
                          border: Border.all(color: Colors.amber[200]!),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(Icons.info_outline, color: Colors.amber[800], size: 16),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'Geotagging Tip: Ensure location is captured above so photos can be verified successfully on the server.',
                                style: TextStyle(color: Colors.amber[900], fontSize: 11, fontWeight: FontWeight.w500),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // 4. Owner Profile Card
              Card(
                elevation: 3,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                margin: const EdgeInsets.only(bottom: 16),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildSectionHeader('Owner Profile (Optional)', Icons.person),
                      TextFormField(
                        controller: _ownerNameController,
                        decoration: _buildInputDecoration(
                          isOwnerTouched ? 'Owner Name *' : 'Owner Name',
                          prefixIcon: Icons.badge,
                        ),
                        validator: (v) {
                          if (isOwnerTouched && (v == null || v.trim().isEmpty)) {
                            return 'Owner Name is required';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        isExpanded: true,
                        value: _genders.contains(ownerGender) ? ownerGender : null,
                        hint: const Text('Select Option'),
                        items: _genders
                            .map((g) => DropdownMenuItem(value: g, child: Text(g)))
                            .toList(),
                        onChanged: (v) {
                          setState(() {
                            ownerGender = v;
                          });
                        },
                        decoration: _buildInputDecoration(
                          isOwnerTouched ? 'Owner Type / Gender *' : 'Owner Type / Gender',
                          prefixIcon: Icons.people_outline,
                        ),
                        validator: (v) {
                          if (isOwnerTouched && (v == null || v.isEmpty)) {
                            return 'Owner Type / Gender is required';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _ownerPhoneController,
                        decoration: _buildInputDecoration(
                          isOwnerTouched && _ownerEmailController.text.trim().isEmpty
                              ? 'Owner Phone *'
                              : 'Owner Phone',
                          prefixIcon: Icons.phone,
                        ),
                        keyboardType: TextInputType.phone,
                        validator: (v) {
                          if (isOwnerTouched &&
                              _ownerEmailController.text.trim().isEmpty &&
                              (v == null || v.trim().isEmpty)) {
                            return 'Phone is required if Email is empty';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _ownerEmailController,
                        decoration: _buildInputDecoration(
                          isOwnerTouched && _ownerPhoneController.text.trim().isEmpty
                              ? 'Owner Email *'
                              : 'Owner Email',
                          prefixIcon: Icons.email,
                        ),
                        keyboardType: TextInputType.emailAddress,
                        validator: (v) {
                          final emailVal = v?.trim() ?? '';
                          if (isOwnerTouched &&
                              _ownerPhoneController.text.trim().isEmpty &&
                              emailVal.isEmpty) {
                            return 'Email is required if Phone is empty';
                          }
                          if (emailVal.isNotEmpty) {
                            final emailRegex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
                            if (!emailRegex.hasMatch(emailVal)) {
                              return 'Enter a valid email address';
                            }
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _ownerNinController,
                        decoration: _buildInputDecoration('Owner NIN', prefixIcon: Icons.fingerprint),
                      ),
                    ],
                  ),
                ),
              ),

              // 5. Occupant Profile Card
              Card(
                elevation: 3,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                margin: const EdgeInsets.only(bottom: 24),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildSectionHeader('Occupant Profile (Optional)', Icons.person_pin),
                      TextFormField(
                        controller: _occupantNameController,
                        decoration: _buildInputDecoration('Occupant Name', prefixIcon: Icons.badge_outlined),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _occupantPhoneController,
                        decoration: _buildInputDecoration('Occupant Phone', prefixIcon: Icons.phone_android),
                        keyboardType: TextInputType.phone,
                      ),
                    ],
                  ),
                ),
              ),

              // Register/Edit Button
              ElevatedButton(
                onPressed: _saveEstablishment,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0A5C36),
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 54),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  elevation: 2,
                ),
                child: Text(
                  isEdit ? 'Save Changes' : 'Register Establishment (Save Offline)',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}
