class Env {
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8080/api',
  );

  static const String appTitle = String.fromEnvironment(
    'APP_TITLE',
    defaultValue: 'Taraba - URCS',
  );

  static const String appSubtitle = String.fromEnvironment(
    'APP_SUBTITLE',
    defaultValue: 'Unified Revenue Collection System',
  );

  static const String appOrganization = String.fromEnvironment(
    'APP_ORGANIZATION',
    defaultValue: 'Field Data Collector - V1.0.0',
  );
}
