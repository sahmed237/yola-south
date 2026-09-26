import 'dart:convert';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/establishment.dart';
import '../models/user.dart';

class DatabaseHelper {
  static final DatabaseHelper _instance = DatabaseHelper._internal();
  factory DatabaseHelper() => _instance;
  static Database? _database;

  DatabaseHelper._internal();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async {
    String path = join(await getDatabasesPath(), 'revenue_system.db');
    return await openDatabase(
      path,
      version: 9,
      onCreate: (db, version) async {
        await db.execute(
          '''CREATE TABLE establishments(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            type TEXT,
            size TEXT,
            lga TEXT,
            ward TEXT,
            lat REAL,
            lng REAL,
            occupant_name TEXT,
            occupant_phone TEXT,
            sync_status TEXT,
            inside_metropolis INTEGER DEFAULT 0,
            street_address TEXT,
            house_number TEXT,
            city TEXT,
            postal_code TEXT,
            owner_name TEXT,
            owner_gender TEXT,
            owner_phone TEXT,
            owner_email TEXT,
            owner_nin TEXT,
            base_year INTEGER,
            images TEXT,
            server_id INTEGER,
            rejection_remarks TEXT
          )''',
        );
        await db.execute(
          '''CREATE TABLE users(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            password_hash TEXT,
            name TEXT,
            token TEXT,
            last_login_at TEXT,
            permissions TEXT
          )''',
        );
        await db.execute(
          '''CREATE TABLE lgas(
            id INTEGER PRIMARY KEY,
            name TEXT
          )''',
        );
        await db.execute(
          '''CREATE TABLE wards(
            id INTEGER PRIMARY KEY,
            lga_id INTEGER,
            name TEXT
          )''',
        );
        await db.execute(
          '''CREATE TABLE establishment_types(
            id INTEGER PRIMARY KEY,
            value TEXT
          )''',
        );
        await db.execute(
          '''CREATE TABLE establishment_sizes(
            id INTEGER PRIMARY KEY,
            value TEXT
          )''',
        );
        await db.execute(
          '''CREATE TABLE unpaid_establishments(
            id INTEGER PRIMARY KEY,
            name TEXT,
            type TEXT,
            size TEXT,
            lga TEXT,
            ward TEXT,
            lat REAL,
            lng REAL,
            occupant_name TEXT,
            occupant_phone TEXT,
            inside_metropolis INTEGER DEFAULT 0,
            street_address TEXT,
            house_number TEXT,
            city TEXT,
            postal_code TEXT,
            owner_name TEXT,
            owner_gender TEXT,
            owner_phone TEXT,
            owner_email TEXT,
            owner_nin TEXT,
            base_year INTEGER,
            images TEXT,
            outstanding_amount REAL,
            total_due REAL,
            total_paid REAL
          )''',
        );
      },
      onUpgrade: (db, oldVersion, newVersion) async {
        if (oldVersion < 2) {
          await db.execute(
            '''CREATE TABLE users(
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              username TEXT UNIQUE,
              password_hash TEXT,
              name TEXT,
              token TEXT,
              last_login_at TEXT
            )''',
          );
        }
        if (oldVersion < 3) {
          // Alter Table statements for version 3 (previously added on the shops table)
          await db.execute("ALTER TABLE shops ADD COLUMN inside_metropolis INTEGER DEFAULT 0");
          await db.execute("ALTER TABLE shops ADD COLUMN street_address TEXT");
          await db.execute("ALTER TABLE shops ADD COLUMN house_number TEXT");
          await db.execute("ALTER TABLE shops ADD COLUMN city TEXT");
          await db.execute("ALTER TABLE shops ADD COLUMN postal_code TEXT");
          await db.execute("ALTER TABLE shops ADD COLUMN owner_name TEXT");
          await db.execute("ALTER TABLE shops ADD COLUMN owner_gender TEXT");
          await db.execute("ALTER TABLE shops ADD COLUMN owner_phone TEXT");
          await db.execute("ALTER TABLE shops ADD COLUMN owner_email TEXT");
          await db.execute("ALTER TABLE shops ADD COLUMN owner_nin TEXT");
          await db.execute("ALTER TABLE shops ADD COLUMN base_year INTEGER");
        }
        if (oldVersion < 4) {
          // Rename shops table to establishments to conform with backend terminology
          await db.execute("ALTER TABLE shops RENAME TO establishments");
        }
        if (oldVersion < 5) {
          await db.execute(
            '''CREATE TABLE lgas(
              id INTEGER PRIMARY KEY,
              name TEXT
            )''',
          );
          await db.execute(
            '''CREATE TABLE wards(
              id INTEGER PRIMARY KEY,
              lga_id INTEGER,
              name TEXT
            )''',
          );
          await db.execute(
            '''CREATE TABLE establishment_types(
              id INTEGER PRIMARY KEY,
              value TEXT
            )''',
          );
          await db.execute(
            '''CREATE TABLE establishment_sizes(
              id INTEGER PRIMARY KEY,
              value TEXT
            )''',
          );
        }
        if (oldVersion < 6) {
          await db.execute("ALTER TABLE establishments ADD COLUMN images TEXT");
        }
        if (oldVersion < 7) {
          await db.execute("ALTER TABLE establishments ADD COLUMN server_id INTEGER");
        }
        if (oldVersion < 8) {
          await db.execute("ALTER TABLE establishments ADD COLUMN rejection_remarks TEXT");
        }
        if (oldVersion < 9) {
          await db.execute("ALTER TABLE users ADD COLUMN permissions TEXT");
          await db.execute(
            '''CREATE TABLE unpaid_establishments(
              id INTEGER PRIMARY KEY,
              name TEXT,
              type TEXT,
              size TEXT,
              lga TEXT,
              ward TEXT,
              lat REAL,
              lng REAL,
              occupant_name TEXT,
              occupant_phone TEXT,
              inside_metropolis INTEGER DEFAULT 0,
              street_address TEXT,
              house_number TEXT,
              city TEXT,
              postal_code TEXT,
              owner_name TEXT,
              owner_gender TEXT,
              owner_phone TEXT,
              owner_email TEXT,
              owner_nin TEXT,
              base_year INTEGER,
              images TEXT,
              outstanding_amount REAL,
              total_due REAL,
              total_paid REAL
            )''',
          );
        }
      },
    );
  }

  Future<int> insertEstablishment(Establishment establishment) async {
    Database db = await database;
    return await db.insert('establishments', establishment.toMap());
  }

  Future<int> updateEstablishment(Establishment establishment) async {
    Database db = await database;
    return await db.update(
      'establishments',
      establishment.toMap(),
      where: 'id = ?',
      whereArgs: [establishment.id],
    );
  }

  Future<List<Establishment>> getUnsyncedEstablishments() async {
    Database db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'establishments',
      where: 'sync_status = ?',
      whereArgs: ['pending_sync'],
    );
    return List.generate(maps.length, (i) => Establishment.fromMap(maps[i]));
  }

  Future<void> updateSyncStatus(int id, String status) async {
    Database db = await database;
    await db.update(
      'establishments',
      {'sync_status': status},
      where: 'id = ?',
      whereArgs: [id],
    );
  }

  Future<void> updateSyncStatusAndRemarks(int id, String status, String? remarks) async {
    Database db = await database;
    await db.update(
      'establishments',
      {
        'sync_status': status,
        'rejection_remarks': remarks,
      },
      where: 'id = ?',
      whereArgs: [id],
    );
  }

  Future<List<Establishment>> getAllEstablishments() async {
    Database db = await database;
    final List<Map<String, dynamic>> maps = await db.query('establishments');
    return List.generate(maps.length, (i) => Establishment.fromMap(maps[i]));
  }

  Future<Establishment?> getEstablishmentById(int id) async {
    Database db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'establishments',
      where: 'id = ?',
      whereArgs: [id],
      limit: 1,
    );
    if (maps.isEmpty) return null;
    return Establishment.fromMap(maps.first);
  }

  Future<Establishment?> getEstablishmentByServerId(int serverId) async {
    Database db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'establishments',
      where: 'server_id = ?',
      whereArgs: [serverId],
      limit: 1,
    );
    if (maps.isEmpty) return null;
    return Establishment.fromMap(maps.first);
  }

  Future<void> updateServerId(int localId, int serverId) async {
    Database db = await database;
    await db.update(
      'establishments',
      {'server_id': serverId},
      where: 'id = ?',
      whereArgs: [localId],
    );
  }

  Future<int> insertOrUpdateUser(User user) async {
    Database db = await database;
    return await db.insert(
      'users',
      user.toMap(),
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<User?> getUserByUsername(String username) async {
    Database db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'users',
      where: 'username = ?',
      whereArgs: [username.trim().toLowerCase()],
    );
    if (maps.isEmpty) return null;
    return User.fromMap(maps.first);
  }

  Future<User?> getActiveUser() async {
    Database db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'users',
      orderBy: 'last_login_at DESC',
      limit: 1,
    );
    if (maps.isEmpty) return null;
    return User.fromMap(maps.first);
  }

  Future<void> saveMetadata({
    required List<dynamic> lgas,
    required List<dynamic> types,
    required List<dynamic> sizes,
  }) async {
    Database db = await database;
    await db.transaction((txn) async {
      // Clear existing records to avoid duplicates/stale data
      await txn.delete('lgas');
      await txn.delete('wards');
      await txn.delete('establishment_types');
      await txn.delete('establishment_sizes');

      // Insert LGAs and Wards
      for (var lga in lgas) {
        await txn.insert('lgas', {
          'id': lga['id'],
          'name': lga['name'],
        }, conflictAlgorithm: ConflictAlgorithm.replace);

        if (lga['wards'] != null) {
          for (var ward in lga['wards']) {
            await txn.insert('wards', {
              'id': ward['id'],
              'lga_id': lga['id'],
              'name': ward['name'],
            }, conflictAlgorithm: ConflictAlgorithm.replace);
          }
        }
      }

      // Insert Establishment Types
      for (var type in types) {
        await txn.insert('establishment_types', {
          'id': type['id'],
          'value': type['value'],
        }, conflictAlgorithm: ConflictAlgorithm.replace);
      }

      // Insert Establishment Sizes
      for (var size in sizes) {
        await txn.insert('establishment_sizes', {
          'id': size['id'],
          'value': size['value'],
        }, conflictAlgorithm: ConflictAlgorithm.replace);
      }
    });
  }

  Future<List<Map<String, dynamic>>> getLgas() async {
    Database db = await database;
    return await db.query('lgas', orderBy: 'name');
  }

  Future<List<Map<String, dynamic>>> getWardsForLga(String lgaName) async {
    Database db = await database;
    final List<Map<String, dynamic>> lgaResult = await db.query(
      'lgas',
      columns: ['id'],
      where: 'name = ?',
      whereArgs: [lgaName],
      limit: 1,
    );
    if (lgaResult.isEmpty) return [];
    final int lgaId = lgaResult.first['id'];
    return await db.query(
      'wards',
      columns: ['name'],
      where: 'lga_id = ?',
      whereArgs: [lgaId],
      orderBy: 'name',
    );
  }

  Future<List<String>> getEstablishmentTypes() async {
    Database db = await database;
    final List<Map<String, dynamic>> maps = await db.query('establishment_types', orderBy: 'value');
    return List.generate(maps.length, (i) => maps[i]['value'] as String);
  }

  Future<List<String>> getEstablishmentSizes() async {
    Database db = await database;
    final List<Map<String, dynamic>> maps = await db.query('establishment_sizes', orderBy: 'id');
    return List.generate(maps.length, (i) => maps[i]['value'] as String);
  }

  Future<void> replaceUnpaidEstablishments(List<dynamic> items) async {
    Database db = await database;
    await db.transaction((txn) async {
      await txn.delete('unpaid_establishments');
      for (var item in items) {
        final int? serverId = item['id'];
        if (serverId == null) continue;

        final String typeVal = item['establishment_type']?['value'] ?? '';
        final String sizeVal = item['establishment_size']?['value'] ?? '';
        
        final occupant = item['occupant'];
        final owner = item['owner'];

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

        await txn.insert('unpaid_establishments', {
          'id': serverId,
          'name': item['name'] ?? '',
          'type': typeVal,
          'size': sizeVal,
          'lga': item['lga'] ?? '',
          'ward': item['ward'] ?? '',
          'lat': item['lat'] != null ? double.tryParse(item['lat'].toString()) : null,
          'lng': item['lng'] != null ? double.tryParse(item['lng'].toString()) : null,
          'inside_metropolis': (item['inside_metropolis'] == 1 || item['inside_metropolis'] == true) ? 1 : 0,
          'street_address': item['street_address'] ?? '',
          'house_number': item['house_number'] ?? '',
          'city': item['city'] ?? '',
          'postal_code': item['postal_code'] ?? '',
          'occupant_name': occupant?['name'],
          'occupant_phone': occupant?['phone'],
          'owner_name': owner?['name'],
          'owner_gender': owner?['gender'],
          'owner_phone': owner?['phone'],
          'owner_email': owner?['email'],
          'owner_nin': owner?['nin'],
          'base_year': item['base_year'] ?? DateTime.now().year,
          'images': jsonEncode(imagePaths),
          'outstanding_amount': item['outstanding_amount'] != null ? double.tryParse(item['outstanding_amount'].toString()) : 0.0,
          'total_due': item['total_due'] != null ? double.tryParse(item['total_due'].toString()) : 0.0,
          'total_paid': item['total_paid'] != null ? double.tryParse(item['total_paid'].toString()) : 0.0,
        }, conflictAlgorithm: ConflictAlgorithm.replace);
      }
    });
  }

  Future<List<Map<String, dynamic>>> getUnpaidEstablishments() async {
    Database db = await database;
    return await db.query('unpaid_establishments', orderBy: 'outstanding_amount DESC');
  }

  Future<int> getUnpaidEstablishmentsCount() async {
    Database db = await database;
    final result = await db.rawQuery('SELECT COUNT(*) as count FROM unpaid_establishments');
    return Sqflite.firstIntValue(result) ?? 0;
  }

  Future<void> clearUserData() async {
    Database db = await database;
    await db.transaction((txn) async {
      await txn.delete('establishments');
      await txn.delete('unpaid_establishments');
    });
  }
}
