import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'database_helper.dart';
import 'api_service.dart';
import '../models/establishment.dart';

class SyncService {
  final DatabaseHelper _dbHelper = DatabaseHelper();
  final ApiService _apiService = ApiService();

  Future<void> performSync() async {
    List<Establishment> unsynced = await _dbHelper.getUnsyncedEstablishments();
    if (unsynced.isEmpty) return;

    try {
      final activeUser = await _dbHelper.getActiveUser();
      final token = activeUser?.token;
      
      List<Map<String, dynamic>> payload = [];
      for (var est in unsynced) {
        Map<String, dynamic> json = est.toJson();
        List<String> base64Images = [];
        for (var path in est.images) {
          try {
            final file = File(path);
            if (await file.exists()) {
              final bytes = await file.readAsBytes();
              final base64String = base64Encode(bytes);
              base64Images.add('data:image/jpeg;base64,$base64String');
            }
          } catch (e) {
            debugPrint('Failed to encode image at path $path: $e');
          }
        }
        json['images'] = base64Images;
        payload.add(json);
      }

      final results = await _apiService.syncEstablishments(payload, token);
      for (var result in results['results']) {
        final localId = result['local_id'];
        if (result['status'] == 'synced') {
          final serverId = result['server_id'];
          if (localId != null && serverId != null) {
            await _dbHelper.updateServerId(localId, serverId);
          }
          await _dbHelper.updateSyncStatus(localId, 'synced');
        } else {
          await _dbHelper.updateSyncStatus(localId, 'failed');
        }
      }
    } catch (e) {
      debugPrint('Sync failed: $e');
    }
  }

  Future<void> fetchAndUpdateStatuses() async {
    try {
      final activeUser = await _dbHelper.getActiveUser();
      final token = activeUser?.token;
      if (token == null) return;

      final response = await _apiService.fetchSyncStatus(token);
      final list = response['establishments'] as List<dynamic>?;
      if (list == null) return;

      for (var item in list) {
        final int? serverId = item['id'];
        final String? serverStatus = item['status'];
        if (serverId == null || serverStatus == null) continue;

        // Check if we have this establishment locally
        final localEst = await _dbHelper.getEstablishmentByServerId(serverId);
        
        // Map server status to local syncStatus
        String localSyncStatus = 'synced';
        if (serverStatus == 'rejected') {
          localSyncStatus = 'invalid';
        } else if (serverStatus == 'approved') {
          localSyncStatus = 'synced';
        } else if (serverStatus == 'pending') {
          localSyncStatus = 'synced';
        }

        final String? rejectionRemarks = item['rejection_remarks'] as String?;

        // Map server images to paths (served dynamically via Laravel storage link)
        List<String> imagePaths = [];
        final serverImages = item['images'] as List<dynamic>?;
        if (serverImages != null) {
          for (var img in serverImages) {
            final path = img['image_path'] as String?;
            if (path != null) {
              imagePaths.add('http://127.0.0.1:8080/storage/$path');
            }
          }
        }

        if (localEst != null) {
          // If we have it locally and it's not pending sync, update status and remarks
          if (localEst.syncStatus != 'pending_sync') {
            if (localEst.syncStatus != localSyncStatus || localEst.rejectionRemarks != rejectionRemarks) {
              await _dbHelper.updateSyncStatusAndRemarks(localEst.id!, localSyncStatus, rejectionRemarks);
            }
          }
        } else {
          // If the record does not exist locally (e.g. after reinstall), replicate it!
          final String typeVal = item['establishment_type']?['value'] ?? '';
          final String sizeVal = item['establishment_size']?['value'] ?? '';
          
          final occupant = item['occupant'];
          final owner = item['owner'];

          final newEst = Establishment(
            serverId: serverId,
            name: item['name'] ?? '',
            type: typeVal,
            size: sizeVal,
            lga: item['lga'] ?? '',
            ward: item['ward'] ?? '',
            lat: item['lat'] != null ? double.tryParse(item['lat'].toString()) : null,
            lng: item['lng'] != null ? double.tryParse(item['lng'].toString()) : null,
            insideMetropolis: (item['inside_metropolis'] == 1 || item['inside_metropolis'] == true),
            streetAddress: item['street_address'] ?? '',
            houseNumber: item['house_number'] ?? '',
            city: item['city'] ?? '',
            postalCode: item['postal_code'] ?? '',
            occupantName: occupant?['name'],
            occupantPhone: occupant?['phone'],
            ownerName: owner?['name'],
            ownerGender: owner?['gender'],
            ownerPhone: owner?['phone'],
            ownerEmail: owner?['email'],
            ownerNin: owner?['nin'],
            baseYear: item['base_year'] ?? DateTime.now().year,
            images: imagePaths,
            syncStatus: localSyncStatus,
            rejectionRemarks: rejectionRemarks,
          );
          await _dbHelper.insertEstablishment(newEst);
        }
      }
    } catch (e) {
      debugPrint('Fetch sync status failed: $e');
    }
  }

  Future<void> pullUnpaidTaxes() async {
    try {
      final activeUser = await _dbHelper.getActiveUser();
      final token = activeUser?.token;
      if (token == null) return;

      final response = await _apiService.fetchUnpaidTaxes(token);
      final list = response['establishments'] as List<dynamic>?;
      if (list == null) return;

      await _dbHelper.replaceUnpaidEstablishments(list);
    } catch (e) {
      debugPrint('Pull unpaid taxes failed: $e');
      rethrow;
    }
  }
}
