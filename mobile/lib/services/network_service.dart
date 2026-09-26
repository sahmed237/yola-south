import 'dart:async';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import '../config/env.dart';

class NetworkService {
  static final NetworkService _instance = NetworkService._internal();
  factory NetworkService() => _instance;

  NetworkService._internal() {
    _startMonitoring();
  }

  final ValueNotifier<bool> isOnline = ValueNotifier<bool>(true);
  Timer? _timer;

  void _startMonitoring() {
    checkConnectivity();
    // Periodically verify connection status every 4 seconds
    _timer = Timer.periodic(const Duration(seconds: 4), (_) => checkConnectivity());
  }

  Future<bool> checkConnectivity() async {
    bool online = false;
    try {
      // 1. Try to connect to a public DNS IP (Google DNS) via raw Socket to bypass OS-level DNS cache lag
      final socket = await Socket.connect('8.8.8.8', 53, timeout: const Duration(seconds: 2));
      socket.destroy();
      online = true;
    } catch (_) {
      // Fallback: Check local development server URL reachability (useful for offline local development)
      try {
        final uri = Uri.parse('${Env.apiBaseUrl}/v1');
        await http.get(
          uri,
          headers: {'Host': 'urcs.local'},
        ).timeout(const Duration(seconds: 2));
        online = true;
      } catch (_) {
        online = false;
      }
    }

    if (isOnline.value != online) {
      isOnline.value = online;
    }
    return online;
  }

  void dispose() {
    _timer?.cancel();
  }
}
