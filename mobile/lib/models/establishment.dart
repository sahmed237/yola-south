import 'dart:convert';

class Establishment {
  int? id;
  int? serverId;
  String name;
  String type;
  String size;
  String lga;
  String ward;
  double? lat;
  double? lng;
  String? occupantName;
  String? occupantPhone;
  String syncStatus; // pending_sync, synced, failed, invalid
  String? rejectionRemarks;


  // New Address Fields
  bool insideMetropolis;
  String streetAddress;
  String houseNumber;
  String city;
  String postalCode;

  // New Owner Fields
  String? ownerName;
  String? ownerGender;
  String? ownerPhone;
  String? ownerEmail;
  String? ownerNin;

  // Base Year
  int baseYear;

  // Local Image Paths
  List<String> images;

  Establishment({
    this.id,
    this.serverId,
    required this.name,
    required this.type,
    required this.size,
    required this.lga,
    required this.ward,
    this.lat,
    this.lng,
    this.occupantName,
    this.occupantPhone,
    this.syncStatus = 'pending_sync',
    this.insideMetropolis = false,
    required this.streetAddress,
    required this.houseNumber,
    required this.city,
    required this.postalCode,
    this.ownerName,
    this.ownerGender,
    this.ownerPhone,
    this.ownerEmail,
    this.ownerNin,
    required this.baseYear,
    this.images = const [],
    this.rejectionRemarks,
  });

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'server_id': serverId,
      'name': name,
      'type': type,
      'size': size,
      'lga': lga,
      'ward': ward,
      'lat': lat,
      'lng': lng,
      'occupant_name': occupantName,
      'occupant_phone': occupantPhone,
      'sync_status': syncStatus,
      'inside_metropolis': insideMetropolis ? 1 : 0,
      'street_address': streetAddress,
      'house_number': houseNumber,
      'city': city,
      'postal_code': postalCode,
      'owner_name': ownerName,
      'owner_gender': ownerGender,
      'owner_phone': ownerPhone,
      'owner_email': ownerEmail,
      'owner_nin': ownerNin,
      'base_year': baseYear,
      'images': jsonEncode(images),
      'rejection_remarks': rejectionRemarks,
    };
  }

  factory Establishment.fromMap(Map<String, dynamic> map) {
    List<String> parsedImages = [];
    if (map['images'] != null && map['images'] is String) {
      try {
        parsedImages = List<String>.from(jsonDecode(map['images'] as String));
      } catch (e) {
        // Ignore or fallback
      }
    }
    return Establishment(
      id: map['id'] as int?,
      serverId: map['server_id'] as int?,
      name: map['name'] as String,
      type: map['type'] as String,
      size: map['size'] as String,
      lga: map['lga'] as String,
      ward: map['ward'] as String,
      lat: map['lat'] as double?,
      lng: map['lng'] as double?,
      occupantName: map['occupant_name'] as String?,
      occupantPhone: map['occupant_phone'] as String?,
      syncStatus: map['sync_status'] as String,
      insideMetropolis: (map['inside_metropolis'] ?? 0) == 1,
      streetAddress: map['street_address'] as String? ?? '',
      houseNumber: map['house_number'] as String? ?? '',
      city: map['city'] as String? ?? '',
      postalCode: map['postal_code'] as String? ?? '',
      ownerName: map['owner_name'] as String?,
      ownerGender: map['owner_gender'] as String?,
      ownerPhone: map['owner_phone'] as String?,
      ownerEmail: map['owner_email'] as String?,
      ownerNin: map['owner_nin'] as String?,
      baseYear: map['base_year'] as int? ?? DateTime.now().year,
      images: parsedImages,
      rejectionRemarks: map['rejection_remarks'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'local_id': id,
      'server_id': serverId,
      'name': name,
      'type': type,
      'size': size,
      'lga': lga,
      'ward': ward,
      'lat': lat,
      'lng': lng,
      'occupant_name': occupantName,
      'occupant_phone': occupantPhone,
      'inside_metropolis': insideMetropolis,
      'street_address': streetAddress,
      'house_number': houseNumber,
      'city': city,
      'postal_code': postalCode,
      'owner_name': ownerName,
      'owner_gender': ownerGender,
      'owner_phone': ownerPhone,
      'owner_email': ownerEmail,
      'owner_nin': ownerNin,
      'base_year': baseYear,
      'images': images,
      'rejection_remarks': rejectionRemarks,
    };
  }
}
