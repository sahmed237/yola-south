import 'dart:convert';

class User {
  final int? id;
  final String username;
  final String passwordHash;
  final String name;
  final String token;
  final DateTime lastLoginAt;
  final List<String> permissions;

  User({
    this.id,
    required this.username,
    required this.passwordHash,
    required this.name,
    required this.token,
    required this.lastLoginAt,
    this.permissions = const [],
  });

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'username': username,
      'password_hash': passwordHash,
      'name': name,
      'token': token,
      'last_login_at': lastLoginAt.toIso8601String(),
      'permissions': jsonEncode(permissions),
    };
  }

  factory User.fromMap(Map<String, dynamic> map) {
    List<String> parsedPermissions = [];
    if (map['permissions'] != null && map['permissions'] is String) {
      try {
        parsedPermissions = List<String>.from(jsonDecode(map['permissions'] as String));
      } catch (e) {
        // Ignore or fallback
      }
    }
    return User(
      id: map['id'] as int?,
      username: map['username'] as String,
      passwordHash: map['password_hash'] as String,
      name: map['name'] as String,
      token: map['token'] as String,
      lastLoginAt: DateTime.parse(map['last_login_at'] as String),
      permissions: parsedPermissions,
    );
  }
}
