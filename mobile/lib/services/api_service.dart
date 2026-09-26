import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/env.dart';

class HttpException implements Exception {
  final int statusCode;
  final String message;
  final String? errorCode;
  final String? email;

  HttpException({
    required this.statusCode,
    required this.message,
    this.errorCode,
    this.email,
  });

  @override
  String toString() => message;
}

class ApiService {
  static const String baseUrl = '${Env.apiBaseUrl}/v1';

  Future<Map<String, dynamic>> loginOnline(String username, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/login'),
        headers: {
          'Content-Type': 'application/json',
          'Host': 'urcs.local',
        },
        body: jsonEncode({
          'username': username,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        try {
          final data = jsonDecode(response.body);
          throw HttpException(
            statusCode: response.statusCode,
            message: data['message'] ?? 'Authentication failed',
            errorCode: data['error_code'],
            email: data['email'],
          );
        } catch (e) {
          if (e is HttpException) rethrow;
          throw HttpException(
            statusCode: response.statusCode,
            message: 'Failed to authenticate: Status code ${response.statusCode}',
          );
        }
      }
    } catch (e) {
      if (e is HttpException) rethrow;
      throw Exception('Network error: $e');
    }
  }

  Future<Map<String, dynamic>> syncEstablishments(List<Map<String, dynamic>> establishmentsJson, String? token) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/sync'),
        headers: {
          'Content-Type': 'application/json',
          'Host': 'urcs.local', // Routes correctly to the WampServer virtual host
          if (token != null) 'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'establishments': establishmentsJson,
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to sync: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }

  Future<Map<String, dynamic>> fetchSyncStatus(String? token) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/sync/status'),
        headers: {
          'Content-Type': 'application/json',
          'Host': 'urcs.local',
          if (token != null) 'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to fetch sync status: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }

  Future<Map<String, dynamic>> fetchUnpaidTaxes(String token) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/sync/unpaid'),
        headers: {
          'Content-Type': 'application/json',
          'Host': 'urcs.local',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        throw Exception('Failed to fetch unpaid taxes: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }
}
