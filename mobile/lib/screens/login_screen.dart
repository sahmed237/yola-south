import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:crypto/crypto.dart';
import '../config/env.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/database_helper.dart';
import '../services/network_service.dart';
import 'home_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _apiService = ApiService();
  final _dbHelper = DatabaseHelper();

  bool _isLoading = false;
  bool _isDeviceOnline = true;
  String? _errorMessage;
  bool _obscurePassword = true;

  @override
  void initState() {
    super.initState();
    NetworkService().isOnline.addListener(_onNetworkChanged);
    _isDeviceOnline = NetworkService().isOnline.value;
  }

  void _onNetworkChanged() {
    if (mounted) {
      setState(() {
        _isDeviceOnline = NetworkService().isOnline.value;
      });
    }
  }

  @override
  void dispose() {
    NetworkService().isOnline.removeListener(_onNetworkChanged);
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _checkConnectivity() async {
    await NetworkService().checkConnectivity();
  }

  String _hashPassword(String password) {
    final bytes = utf8.encode('${password}nigerian_revenue_system_salt_2026');
    return sha256.convert(bytes).toString();
  }

  void _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final username = _usernameController.text.trim();
    final password = _passwordController.text;

    // 1. Re-check connectivity immediately on submit
    await _checkConnectivity();

    if (_isDeviceOnline) {
      // ONLINE AUTHENTICATION
      try {
        final result = await _apiService.loginOnline(username, password);
        final token = result['token'] ?? 'mock-token';
        final List<String> permissions = List<String>.from(result['permissions'] ?? []);
        
        // Cache user info and hashed password for offline fallback
        final user = User(
          username: username.toLowerCase(),
          passwordHash: _hashPassword(password),
          name: result['name'] ?? username.toUpperCase(),
          token: token,
          lastLoginAt: DateTime.now(),
          permissions: permissions,
        );

        // Check if username changed from the last active user
        final lastActiveUser = await _dbHelper.getActiveUser();
        if (lastActiveUser != null && lastActiveUser.username.toLowerCase() != username.toLowerCase()) {
          // Clear cached establishments and unpaid taxes of the previous user to prevent leakage
          await _dbHelper.clearUserData();
        }

        await _dbHelper.insertOrUpdateUser(user);

        // Cache metadata for offline registration use
        final List<dynamic> lgas = result['lgas'] ?? [];
        final List<dynamic> types = result['establishment_types'] ?? [];
        final List<dynamic> sizes = result['establishment_sizes'] ?? [];
        await _dbHelper.saveMetadata(
          lgas: lgas,
          types: types,
          sizes: sizes,
        );
        
        if (!mounted) return;
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const HomeScreen()),
        );
      } catch (e) {
        if (e is HttpException) {
          setState(() {
            _errorMessage = e.message;
            _isLoading = false;
          });
        } else {
          // Check if a local cached profile exists first
          final cachedUser = await _dbHelper.getUserByUsername(username);
          if (cachedUser == null) {
            setState(() {
              _errorMessage = 'Could not connect to the server: $e\n\n(Note: Ensure your development server is running and adb port forwarding is active).';
              _isLoading = false;
            });
          } else {
            _attemptOfflineFallback(username, password);
          }
        }
      }
    } else {
      // OFFLINE AUTHENTICATION
      _attemptOfflineFallback(username, password);
    }
  }

  void _attemptOfflineFallback(String username, String password) async {
    try {
      final user = await _dbHelper.getUserByUsername(username);
      
      if (user == null) {
        setState(() {
          _errorMessage = 'No cached profile found. You must log in online first.';
          _isLoading = false;
        });
        return;
      }

      // Check password hash
      final enteredHash = _hashPassword(password);
      if (user.passwordHash != enteredHash) {
        setState(() {
          _errorMessage = 'Incorrect password (offline verification).';
          _isLoading = false;
        });
        return;
      }

      // Check session expiry (14 days)
      final difference = DateTime.now().difference(user.lastLoginAt).inDays;
      if (difference >= 14) {
        setState(() {
          _errorMessage = 'Offline session expired. Please connect to the internet to verify.';
          _isLoading = false;
        });
        return;
      }

      // Check if username changed from the last active user
      final lastActiveUser = await _dbHelper.getActiveUser();
      if (lastActiveUser != null && lastActiveUser.username.toLowerCase() != username.toLowerCase()) {
        // Clear cached establishments and unpaid taxes of the previous user to prevent leakage
        await _dbHelper.clearUserData();
      }

      // Update lastLoginAt to make them the active user
      final updatedUser = User(
        id: user.id,
        username: user.username,
        passwordHash: user.passwordHash,
        name: user.name,
        token: user.token,
        lastLoginAt: DateTime.now(),
        permissions: user.permissions,
      );
      await _dbHelper.insertOrUpdateUser(updatedUser);

      // Successful offline login
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Logged in offline. Session valid for ${14 - difference} more days.'),
          backgroundColor: Colors.amber[800],
          duration: const Duration(seconds: 4),
        ),
      );

      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const HomeScreen()),
      );
    } catch (dbError) {
      setState(() {
        _errorMessage = 'Local database verification failed: $dbError';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    // Government Palette
    const primaryGreen = Color(0xFF0A5C36); // Nigerian Emerald Forest Green
    const secondaryGold = Color(0xFFC5A059); // Classic Gold
    const lightBg = Color(0xFFF9FBF9);

    return Scaffold(
      backgroundColor: lightBg,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 28.0, vertical: 20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const SizedBox(height: 30),
              
              // Nigerian Coat of Arms / Dummy Logo
              Container(
                width: 140,
                height: 140,
                decoration: BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.08),
                      blurRadius: 15,
                      offset: const Offset(0, 5),
                    )
                  ],
                ),
                padding: const EdgeInsets.all(12),
                child: Image.asset(
                  'assets/logo.png',
                  fit: BoxFit.contain,
                  errorBuilder: (context, error, stackTrace) {
                    // Fallback if image asset is not yet loaded/found
                    return const Icon(
                      Icons.account_balance,
                      size: 64,
                      color: primaryGreen,
                    );
                  },
                ),
              ),
              const SizedBox(height: 24),

              // Government Header
              const Text(
                Env.appTitle,
                style: TextStyle(
                  color: primaryGreen,
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 1.2,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                Env.appSubtitle,
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Colors.grey,
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(height: 28),

              // Network Status Banner
              AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
                decoration: BoxDecoration(
                  color: _isDeviceOnline 
                      ? primaryGreen.withOpacity(0.1) 
                      : Colors.orange[800]!.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: _isDeviceOnline 
                        ? primaryGreen.withOpacity(0.3) 
                        : Colors.orange[800]!.withOpacity(0.3),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      _isDeviceOnline ? Icons.wifi : Icons.wifi_off,
                      color: _isDeviceOnline ? primaryGreen : Colors.orange[800],
                      size: 20,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        _isDeviceOnline 
                            ? 'Online - Live Database Connection' 
                            : 'Offline Mode Active - Local Authenticator',
                        style: TextStyle(
                          color: _isDeviceOnline ? primaryGreen : Colors.orange[800],
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                        ),
                      ),
                    ),
                    if (!_isDeviceOnline)
                      IconButton(
                        constraints: const BoxConstraints(),
                        padding: EdgeInsets.zero,
                        icon: Icon(Icons.refresh, color: Colors.orange[800], size: 18),
                        onPressed: _checkConnectivity,
                      ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              // Login Form Card
              Card(
                margin: EdgeInsets.zero,
                elevation: 4,
                shadowColor: Colors.black.withOpacity(0.1),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: BorderSide(color: Colors.grey[200]!),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(24.0),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        if (_errorMessage != null) ...[
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.red[55],
                              border: Border.all(color: Colors.red[200]!),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              children: [
                                Icon(Icons.error_outline, color: Colors.red[700]),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    _errorMessage!,
                                    style: TextStyle(color: Colors.red[900], fontSize: 13),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),
                        ],
                        
                        TextFormField(
                          controller: _usernameController,
                          decoration: InputDecoration(
                            labelText: 'Officer Username',
                            prefixIcon: const Icon(Icons.person_outline, color: primaryGreen),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: primaryGreen, width: 2),
                            ),
                          ),
                          validator: (value) {
                            if (value == null || value.trim().isEmpty) {
                              return 'Please enter your username';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 20),
                        
                        TextFormField(
                          controller: _passwordController,
                          obscureText: _obscurePassword,
                          decoration: InputDecoration(
                            labelText: 'Security Password',
                            prefixIcon: const Icon(Icons.lock_outline, color: primaryGreen),
                            suffixIcon: IconButton(
                              icon: Icon(
                                _obscurePassword ? Icons.visibility_off : Icons.visibility,
                                color: primaryGreen,
                              ),
                              onPressed: () {
                                setState(() {
                                  _obscurePassword = !_obscurePassword;
                                });
                              },
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: primaryGreen, width: 2),
                            ),
                          ),
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Please enter your password';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 30),
                        
                        ElevatedButton(
                          onPressed: _isLoading ? null : _handleLogin,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: primaryGreen,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 2,
                          ),
                          child: _isLoading
                              ? const SizedBox(
                                  height: 20,
                                  width: 20,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                  ),
                                )
                              : const Text(
                                  'AUTHENTICATE & ENTER',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                    letterSpacing: 1.1,
                                  ),
                                ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              
              const SizedBox(height: 40),
              
              // Gold footer line
              Container(
                width: 60,
                height: 4,
                decoration: BoxDecoration(
                  color: secondaryGold,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 10),
              
              const Text(
                Env.appOrganization,
                style: TextStyle(
                  color: Colors.grey,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
