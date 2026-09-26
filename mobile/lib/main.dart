import 'package:flutter/material.dart';
import 'screens/login_screen.dart';

void main() {
  runApp(const RevenueApp());
}

class RevenueApp extends StatelessWidget {
  const RevenueApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Revenue Collection System',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0A5C36),
          primary: const Color(0xFF0A5C36),
          secondary: const Color(0xFFC5A059),
        ),
        useMaterial3: true,
        appBarTheme: const AppBarTheme(
          backgroundColor: Color(0xFF0A5C36),
          foregroundColor: Colors.white,
          elevation: 0,
        ),
      ),
      home: const LoginScreen(),
    );
  }
}
