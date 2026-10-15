/**
 * Build .pot + ca/es_ES .po from extracted English strings.
 * Usage: node scripts/generate-i18n.cjs
 * Expects scripts/_strings.txt (string list for regenerating catalogs).
 *
 * @package TSO_Login_Organizer
 */
'use strict';

const fs = require( 'fs' );
const path = require( 'path' );

const dir = path.join( __dirname, '..', 'languages' );
const domain = 'tso-login-organizer';
const stringsPath = path.join( __dirname, '_strings.txt' );

const extra = [
	'Frontend login, registration and account pages with optional premium modules. Drop-in replacement for Theme My Login.',
];

const strings = [
	...extra,
	...fs.readFileSync( stringsPath, 'utf8' ).split( /\r?\n/ ).map( ( s ) => s.trim() ).filter( Boolean ),
];
const unique = [ ...new Set( strings ) ].sort( ( a, b ) => ( a < b ? -1 : a > b ? 1 : 0 ) );

const ca = {
	'Frontend login, registration and account pages with optional premium modules. Drop-in replacement for Theme My Login.':
		'Pàgines frontend d’inici de sessió, registre i compte amb mòduls premium opcionals. Substitut de Theme My Login.',
	'<strong>Error:</strong> Captcha verification failed.': '<strong>Error:</strong> La verificació del captcha ha fallat.',
	'<strong>Error:</strong> Enter your two-factor authentication code.': '<strong>Error:</strong> Introduïu el codi d’autenticació de dos factors.',
	'<strong>Error:</strong> Invalid two-factor code.': '<strong>Error:</strong> Codi de dos factors no vàlid.',
	'<strong>Error:</strong> Password must be at least %d characters.': '<strong>Error:</strong> La contrasenya ha de tenir almenys %d caràcters.',
	'<strong>Error:</strong> Passwords do not match.': '<strong>Error:</strong> Les contrasenyes no coincideixen.',
	'<strong>Error:</strong> Please complete the captcha.': '<strong>Error:</strong> Completeu el captcha.',
	'<strong>Error:</strong> Please confirm your email before logging in.': '<strong>Error:</strong> Confirmeu el correu abans d’iniciar la sessió.',
	'<strong>Error:</strong> Please enter a password.': '<strong>Error:</strong> Introduïu una contrasenya.',
	'<strong>Error:</strong> Please enter a username, not an email address.': '<strong>Error:</strong> Introduïu un nom d’usuari, no un correu.',
	'<strong>Error:</strong> Please enter a valid email address.': '<strong>Error:</strong> Introduïu un correu electrònic vàlid.',
	'<strong>Error:</strong> Too many failed login attempts. Please try again later.': '<strong>Error:</strong> Massa intents fallits. Torneu-ho a provar més tard.',
	'<strong>Error:</strong> User registration is currently not allowed.': '<strong>Error:</strong> El registre d’usuaris no està permès actualment.',
	'<strong>Error:</strong> Your account is awaiting admin approval.': '<strong>Error:</strong> El vostre compte espera l’aprovació de l’administrador.',
	'Add to favorites': 'Afegeix als preferits',
	'Add to Menu': 'Afegeix al menú',
	'Admin approval': 'Aprovació de l’administrador',
	AJAX: 'AJAX',
	'Allow users to set a password at registration': 'Permetre que els usuaris defineixin una contrasenya al registre',
	'Anyone can register': 'Qualsevol pot registrar-se',
	'Authentication code': 'Codi d’autenticació',
	'Authentication code (if enabled)': 'Codi d’autenticació (si està activat)',
	Authenticator: 'Autenticador',
	'Howdy, %s': 'Hola, %s',
	'<strong>Error:</strong> The passwords do not match.': '<strong>Error:</strong> Les contrasenyes no coincideixen.',
	'Auto login': 'Inici de sessió automàtic',
	'Avatar file is too large.': 'El fitxer de l’avatar és massa gran.',
	'Avatar updated.': 'Avatar actualitzat.',
	Avatars: 'Avatars',
	'Block wp-admin': 'Bloquejar wp-admin',
	'Brute force': 'Força bruta',
	'Check your email': 'Consulteu el correu',
	'Check your email for the confirmation link.': 'Consulteu el correu per al enllaç de confirmació.',
	'Confirm Password': 'Confirmeu la contrasenya',
	'Confirm your email': 'Confirmeu el correu',
	'Continue with Facebook': 'Continua amb Facebook',
	'Continue with Google': 'Continua amb Google',
	'Continue with X': 'Continua amb X',
	Core: 'Nucli',
	'Core settings saved.': 'Configuració del nucli desada.',
	Dashboard: 'Tauler',
	'Default form:': 'Formulari per defecte:',
	'Display Name': 'Nom visible',
	Email: 'Correu electrònic',
	'Email Address': 'Adreça de correu',
	'Email confirmation': 'Confirmació per correu',
	'Email only': 'Només correu',
	Enable: 'Activa',
	'Enable AJAX form submissions': 'Activa l’enviament de formularis per AJAX',
	'Enable lockouts': 'Activa els bloquejos',
	'Enable TOTP (new secret generated on save if empty)': 'Activa TOTP (es genera un secret nou en desar si està buit)',
	Favorites: 'Preferits',
	'Get New Password': 'Obtén una contrasenya nova',
	'Hide wp-login.php': 'Amaga wp-login.php',
	'HTML emails': 'Correus HTML',
	'Import Theme My Login free settings (AJAX, login type, registration, passwords, auto-login, slugs). Shortcode [theme-my-login] is already aliased. Deactivate Theme My Login after verifying URLs.':
		'Importa la configuració gratuïta de Theme My Login (AJAX, tipus d’inici de sessió, registre, contrasenyes, auto-login, slugs). El shortcode [theme-my-login] ja està com a àlies. Desactiveu Theme My Login després de verificar les URL.',
	'Import Theme My Login settings': 'Importa la configuració de Theme My Login',
	'Invalid logout request.': 'Sol·licitud de tancament de sessió no vàlida.',
	'Invalid social login request.': 'Sol·licitud d’inici de sessió social no vàlida.',
	'Lockout minutes': 'Minuts de bloqueig',
	'Log In': 'Inicia la sessió',
	'Log in': 'Inicia la sessió',
	'Log Out': 'Tanca la sessió',
	'Log users in after registration': 'Inicia la sessió dels usuaris després del registre',
	'Login type': 'Tipus d’inici de sessió',
	'Login, register or account links.': 'Enllaços d’inici de sessió, registre o compte.',
	'Lost Password': 'Contrasenya perduda',
	'Lost your password?': 'Heu oblidat la contrasenya?',
	Mailchimp: 'Mailchimp',
	'Max attempts': 'Màxim d’intents',
	'Max size (KB)': 'Mida màxima (KB)',
	'May collide with Swiss Knife, Kadence Security or CleanTalk. Off by default.':
		'Pot col·lidir amb Swiss Knife, Kadence Security o CleanTalk. Desactivat per defecte.',
	Membership: 'Membresia',
	Migration: 'Migració',
	'Min password length': 'Longitud mínima de la contrasenya',
	Moderation: 'Moderació',
	'Module settings saved.': 'Configuració del mòdul desada.',
	Modules: 'Mòduls',
	'Modules updated. Collision-prone modules stay off until you enable them.':
		'Mòduls actualitzats. Els que poden col·lidir resten desactivats fins que els activeu.',
	'New Password': 'Contrasenya nova',
	'New user body': 'Cos del correu de nou usuari',
	'New user subject': 'Assumpte del correu de nou usuari',
	'No extra settings for this module.': 'Aquest mòdul no té opcions addicionals.',
	'No favorites yet.': 'Encara no hi ha preferits.',
	'No TML options detected (you can still use this plugin with its own defaults).':
		'No s’han detectat opcions de TML (podeu usar el plugin amb els valors per defecte).',
	'Not found.': 'No s’ha trobat.',
	Notifications: 'Notificacions',
	'Other security/spam plugins were detected. Enable colliding modules only if you know they will not conflict.':
		'S’han detectat altres plugins de seguretat/antispam. Activeu mòduls conflictus només si sabeu que no xocaran.',
	Password: 'Contrasenya',
	Passwords: 'Contrasenyes',
	'Please log in to view this content.': 'Inicieu la sessió per veure aquest contingut.',
	'Possible collisions detected: %s. Keep Security, Captcha and 2FA off unless you disable overlapping features elsewhere.':
		'Possible col·lisió detectada: %s. Deixeu Seguretat, Captcha i 2FA desactivats si ja teniu funcions equivalents en un altre lloc.',
	'Private site': 'Lloc privat',
	Profile: 'Perfil',
	'Profile picture': 'Imatge de perfil',
	'Profile updated.': 'Perfil actualitzat.',
	Profiles: 'Perfils',
	reCAPTCHA: 'reCAPTCHA',
	'Redirect users without edit_posts to the profile page': 'Redirigeix usuaris sense edit_posts a la pàgina de perfil',
	Redirection: 'Redirecció',
	Register: 'Registra’t',
	'Registration complete. You may now log in.': 'Registre complet. Ja podeu iniciar la sessió.',
	'Registration confirmation will be emailed to you.': 'La confirmació del registre s’enviarà al vostre correu.',
	'Registration type': 'Tipus de registre',
	'Remember Me': 'Recorda’m',
	'Remove from favorites': 'Treu dels preferits',
	'Require 2FA for roles (users still need a secret in their profile):':
		'Exigeix 2FA per a rols (els usuaris encara necessiten un secret al perfil):',
	'Require login for all pages': 'Exigeix inici de sessió a totes les pàgines',
	'Reset body': 'Cos del correu de restabliment',
	'Reset Password': 'Restableix la contrasenya',
	Restrictions: 'Restriccions',
	'Return 404 on wp-login.php (do not combine with Swiss custom login URL)':
		'Retorna 404 a wp-login.php (no combineu amb l’URL secreta de Swiss Knife)',
	'Save core settings': 'Desa la configuració del nucli',
	'Save module settings': 'Desa la configuració del mòdul',
	'Save modules': 'Desa els mòduls',
	'Save Password': 'Desa la contrasenya',
	'Save Profile': 'Desa el perfil',
	'Secret key': 'Clau secreta',
	'Secret:': 'Secret:',
	Security: 'Seguretat',
	'Sends tokens to Google reCAPTCHA for verification. Leave empty / module off when using CleanTalk.':
		'Envia tokens a Google reCAPTCHA per verificar. Deixeu-ho buit / mòdul desactivat si useu CleanTalk.',
	Settings: 'Configuració',
	'Show checkbox': 'Mostra la casella',
	'Show form links': 'Mostra els enllaços del formulari',
	'Show on posts': 'Mostra a les entrades',
	'Site Admin': 'Administració del lloc',
	'Site key': 'Clau del lloc',
	Slugs: 'Slugs',
	'Social Login': 'Inici de sessió social',
	'Social login did not return an email address.': 'L’inici de sessió social no ha retornat un correu.',
	'Strength indicator': 'Indicador de força',
	'Subscribe to our newsletter': 'Subscriu-te al butlletí',
	'The passwords do not match.': 'Les contrasenyes no coincideixen.',
	'Theme My Login settings imported.': 'S’ha importat la configuració de Theme My Login.',
	'Title:': 'Títol:',
	'TML options detected on this site.': 'S’han detectat opcions de TML en aquest lloc.',
	'TSO Login Organizer': 'TSO Login Organizer',
	'TSO Login Organizer 2FA': 'TSO Login Organizer 2FA',
	'TSOLO Action': 'Acció TSOLO',
	'Two-Factor Authentication': 'Autenticació de dos factors',
	'Unable to send reset email.': 'No s’ha pogut enviar el correu de restabliment.',
	'Unexpected server response. Please try again.': 'Resposta inesperada del servidor. Torneu-ho a provar.',
	'Upload Avatar': 'Puja l’avatar',
	Username: 'Nom d’usuari',
	'Username + email': 'Nom d’usuari + correu',
	'Username only': 'Només nom d’usuari',
	'Username or email': 'Nom d’usuari o correu',
	'Username or Email Address': 'Nom d’usuari o adreça de correu',
	'Welcome, %s!': 'Benvingut/da, %s!',
	'You are now logged out.': 'Heu tancat la sessió.',
	'You do not have permission to view this content.': 'No teniu permís per veure aquest contingut.',
	'Your Data Request': 'La vostra sol·licitud de dades',
	'Your favorites': 'Els vostres preferits',
	'Your password has been reset.': 'S’ha restablert la vostra contrasenya.',
	'Your password reset link appears to be invalid.': 'L’enllaç de restabliment sembla no vàlid.',
	'Your request has been confirmed.': 'La vostra sol·licitud s’ha confirmat.',
};

Object.assign( ca, require( './i18n-admin-extra.cjs' ).ca );
Object.assign( ca, require( './i18n-modules-howto.cjs' ).ca );
Object.assign( ca, require( './i18n-links-social.cjs' ).ca );
Object.assign( ca, require( './i18n-batch-missing.cjs' ).ca );

const es = {
	'Frontend login, registration and account pages with optional premium modules. Drop-in replacement for Theme My Login.':
		'Páginas frontend de inicio de sesión, registro y cuenta con módulos premium opcionales. Sustituto de Theme My Login.',
	'<strong>Error:</strong> Captcha verification failed.': '<strong>Error:</strong> Falló la verificación del captcha.',
	'<strong>Error:</strong> Enter your two-factor authentication code.': '<strong>Error:</strong> Introduce tu código de autenticación de dos factores.',
	'<strong>Error:</strong> Invalid two-factor code.': '<strong>Error:</strong> Código de dos factores no válido.',
	'<strong>Error:</strong> Password must be at least %d characters.': '<strong>Error:</strong> La contraseña debe tener al menos %d caracteres.',
	'<strong>Error:</strong> Passwords do not match.': '<strong>Error:</strong> Las contraseñas no coinciden.',
	'<strong>Error:</strong> Please complete the captcha.': '<strong>Error:</strong> Completa el captcha.',
	'<strong>Error:</strong> Please confirm your email before logging in.': '<strong>Error:</strong> Confirma tu correo antes de iniciar sesión.',
	'<strong>Error:</strong> Please enter a password.': '<strong>Error:</strong> Introduce una contraseña.',
	'<strong>Error:</strong> Please enter a username, not an email address.': '<strong>Error:</strong> Introduce un nombre de usuario, no un correo.',
	'<strong>Error:</strong> Please enter a valid email address.': '<strong>Error:</strong> Introduce una dirección de correo válida.',
	'<strong>Error:</strong> Too many failed login attempts. Please try again later.': '<strong>Error:</strong> Demasiados intentos fallidos. Inténtalo más tarde.',
	'<strong>Error:</strong> User registration is currently not allowed.': '<strong>Error:</strong> El registro de usuarios no está permitido actualmente.',
	'<strong>Error:</strong> Your account is awaiting admin approval.': '<strong>Error:</strong> Tu cuenta espera la aprobación del administrador.',
	'Add to favorites': 'Añadir a favoritos',
	'Add to Menu': 'Añadir al menú',
	'Admin approval': 'Aprobación del administrador',
	AJAX: 'AJAX',
	'Allow users to set a password at registration': 'Permitir que los usuarios elijan una contraseña al registrarse',
	'Anyone can register': 'Cualquiera puede registrarse',
	'Authentication code': 'Código de autenticación',
	'Authentication code (if enabled)': 'Código de autenticación (si está activado)',
	Authenticator: 'Autenticador',
	'Howdy, %s': 'Hola, %s',
	'<strong>Error:</strong> The passwords do not match.': '<strong>Error:</strong> Las contraseñas no coinciden.',
	'Auto login': 'Inicio de sesión automático',
	'Avatar file is too large.': 'El archivo del avatar es demasiado grande.',
	'Avatar updated.': 'Avatar actualizado.',
	Avatars: 'Avatares',
	'Block wp-admin': 'Bloquear wp-admin',
	'Brute force': 'Fuerza bruta',
	'Check your email': 'Revisa tu correo',
	'Check your email for the confirmation link.': 'Revisa tu correo para el enlace de confirmación.',
	'Confirm Password': 'Confirmar contraseña',
	'Confirm your email': 'Confirma tu correo',
	'Continue with Facebook': 'Continuar con Facebook',
	'Continue with Google': 'Continuar con Google',
	'Continue with X': 'Continuar con X',
	Core: 'Núcleo',
	'Core settings saved.': 'Ajustes del núcleo guardados.',
	Dashboard: 'Escritorio',
	'Default form:': 'Formulario predeterminado:',
	'Display Name': 'Nombre visible',
	Email: 'Correo electrónico',
	'Email Address': 'Dirección de correo',
	'Email confirmation': 'Confirmación por correo',
	'Email only': 'Solo correo',
	Enable: 'Activar',
	'Enable AJAX form submissions': 'Activar envío de formularios por AJAX',
	'Enable lockouts': 'Activar bloqueos',
	'Enable TOTP (new secret generated on save if empty)': 'Activar TOTP (se genera un secreto nuevo al guardar si está vacío)',
	Favorites: 'Favoritos',
	'Get New Password': 'Obtener una contraseña nueva',
	'Hide wp-login.php': 'Ocultar wp-login.php',
	'HTML emails': 'Correos HTML',
	'Import Theme My Login free settings (AJAX, login type, registration, passwords, auto-login, slugs). Shortcode [theme-my-login] is already aliased. Deactivate Theme My Login after verifying URLs.':
		'Importar los ajustes gratuitos de Theme My Login (AJAX, tipo de acceso, registro, contraseñas, auto-login, slugs). El shortcode [theme-my-login] ya es un alias. Desactiva Theme My Login tras verificar las URL.',
	'Import Theme My Login settings': 'Importar ajustes de Theme My Login',
	'Invalid logout request.': 'Solicitud de cierre de sesión no válida.',
	'Invalid social login request.': 'Solicitud de inicio de sesión social no válida.',
	'Lockout minutes': 'Minutos de bloqueo',
	'Log In': 'Acceder',
	'Log in': 'Acceder',
	'Log Out': 'Salir',
	'Log users in after registration': 'Iniciar sesión de los usuarios tras el registro',
	'Login type': 'Tipo de acceso',
	'Login, register or account links.': 'Enlaces de acceso, registro o cuenta.',
	'Lost Password': 'Contraseña perdida',
	'Lost your password?': '¿Has perdido tu contraseña?',
	Mailchimp: 'Mailchimp',
	'Max attempts': 'Máximo de intentos',
	'Max size (KB)': 'Tamaño máximo (KB)',
	'May collide with Swiss Knife, Kadence Security or CleanTalk. Off by default.':
		'Puede chocar con Swiss Knife, Kadence Security o CleanTalk. Desactivado por defecto.',
	Membership: 'Membresía',
	Migration: 'Migración',
	'Min password length': 'Longitud mínima de contraseña',
	Moderation: 'Moderación',
	'Module settings saved.': 'Ajustes del módulo guardados.',
	Modules: 'Módulos',
	'Modules updated. Collision-prone modules stay off until you enable them.':
		'Módulos actualizados. Los que pueden chocar permanecen desactivados hasta que los actives.',
	'New Password': 'Contraseña nueva',
	'New user body': 'Cuerpo del correo de nuevo usuario',
	'New user subject': 'Asunto del correo de nuevo usuario',
	'No extra settings for this module.': 'Este módulo no tiene ajustes adicionales.',
	'No favorites yet.': 'Aún no hay favoritos.',
	'No TML options detected (you can still use this plugin with its own defaults).':
		'No se detectaron opciones de TML (puedes usar el plugin con sus valores por defecto).',
	'Not found.': 'No encontrado.',
	Notifications: 'Notificaciones',
	'Other security/spam plugins were detected. Enable colliding modules only if you know they will not conflict.':
		'Se detectaron otros plugins de seguridad/antispam. Activa módulos conflictivos solo si sabes que no chocarán.',
	Password: 'Contraseña',
	Passwords: 'Contraseñas',
	'Please log in to view this content.': 'Inicia sesión para ver este contenido.',
	'Possible collisions detected: %s. Keep Security, Captcha and 2FA off unless you disable overlapping features elsewhere.':
		'Posibles colisiones detectadas: %s. Mantén Seguridad, Captcha y 2FA desactivados si ya tienes funciones equivalentes en otro sitio.',
	'Private site': 'Sitio privado',
	Profile: 'Perfil',
	'Profile picture': 'Imagen de perfil',
	'Profile updated.': 'Perfil actualizado.',
	Profiles: 'Perfiles',
	reCAPTCHA: 'reCAPTCHA',
	'Redirect users without edit_posts to the profile page': 'Redirigir usuarios sin edit_posts a la página de perfil',
	Redirection: 'Redirección',
	Register: 'Registrarse',
	'Registration complete. You may now log in.': 'Registro completado. Ya puedes iniciar sesión.',
	'Registration confirmation will be emailed to you.': 'La confirmación del registro se enviará a tu correo.',
	'Registration type': 'Tipo de registro',
	'Remember Me': 'Recuérdame',
	'Remove from favorites': 'Quitar de favoritos',
	'Require 2FA for roles (users still need a secret in their profile):':
		'Exigir 2FA para roles (los usuarios aún necesitan un secreto en su perfil):',
	'Require login for all pages': 'Exigir inicio de sesión en todas las páginas',
	'Reset body': 'Cuerpo del correo de restablecimiento',
	'Reset Password': 'Restablecer contraseña',
	Restrictions: 'Restricciones',
	'Return 404 on wp-login.php (do not combine with Swiss custom login URL)':
		'Devolver 404 en wp-login.php (no combinar con la URL secreta de Swiss Knife)',
	'Save core settings': 'Guardar ajustes del núcleo',
	'Save module settings': 'Guardar ajustes del módulo',
	'Save modules': 'Guardar módulos',
	'Save Password': 'Guardar contraseña',
	'Save Profile': 'Guardar perfil',
	'Secret key': 'Clave secreta',
	'Secret:': 'Secreto:',
	Security: 'Seguridad',
	'Sends tokens to Google reCAPTCHA for verification. Leave empty / module off when using CleanTalk.':
		'Envía tokens a Google reCAPTCHA para verificar. Déjalo vacío / módulo desactivado si usas CleanTalk.',
	Settings: 'Ajustes',
	'Show checkbox': 'Mostrar casilla',
	'Show form links': 'Mostrar enlaces del formulario',
	'Show on posts': 'Mostrar en entradas',
	'Site Admin': 'Administración del sitio',
	'Site key': 'Clave del sitio',
	Slugs: 'Slugs',
	'Social Login': 'Inicio de sesión social',
	'Social login did not return an email address.': 'El inicio de sesión social no devolvió un correo.',
	'Strength indicator': 'Indicador de fortaleza',
	'Subscribe to our newsletter': 'Suscribirme al boletín',
	'The passwords do not match.': 'Las contraseñas no coinciden.',
	'Theme My Login settings imported.': 'Se importaron los ajustes de Theme My Login.',
	'Title:': 'Título:',
	'TML options detected on this site.': 'Se detectaron opciones de TML en este sitio.',
	'TSO Login Organizer': 'TSO Login Organizer',
	'TSO Login Organizer 2FA': 'TSO Login Organizer 2FA',
	'TSOLO Action': 'Acción TSOLO',
	'Two-Factor Authentication': 'Autenticación de dos factores',
	'Unable to send reset email.': 'No se pudo enviar el correo de restablecimiento.',
	'Unexpected server response. Please try again.': 'Respuesta inesperada del servidor. Inténtalo de nuevo.',
	'Upload Avatar': 'Subir avatar',
	Username: 'Nombre de usuario',
	'Username + email': 'Nombre de usuario + correo',
	'Username only': 'Solo nombre de usuario',
	'Username or email': 'Nombre de usuario o correo',
	'Username or Email Address': 'Nombre de usuario o dirección de correo',
	'Welcome, %s!': '¡Bienvenido/a, %s!',
	'You are now logged out.': 'Has cerrado la sesión.',
	'You do not have permission to view this content.': 'No tienes permiso para ver este contenido.',
	'Your Data Request': 'Tu solicitud de datos',
	'Your favorites': 'Tus favoritos',
	'Your password has been reset.': 'Tu contraseña se ha restablecido.',
	'Your password reset link appears to be invalid.': 'El enlace de restablecimiento parece no ser válido.',
	'Your request has been confirmed.': 'Tu solicitud ha sido confirmada.',
};

Object.assign( es, require( './i18n-admin-extra.cjs' ).es );
Object.assign( es, require( './i18n-modules-howto.cjs' ).es );
Object.assign( es, require( './i18n-links-social.cjs' ).es );
Object.assign( es, require( './i18n-batch-missing.cjs' ).es );

/**
 * Escape PO string.
 *
 * @param {string} s String.
 * @return {string}
 */
function esc( s ) {
	return s.replace( /\\/g, '\\\\' ).replace( /"/g, '\\"' ).replace( /\n/g, '\\n' );
}

/**
 * Build PO content.
 *
 * @param {string} lang Language code.
 * @param {Object<string,string>} map Translations.
 * @return {string}
 */
function buildPo( lang, map ) {
	const header =
		`# TSO Login Organizer — ${ lang }\n` +
		`# Copyright (C) 2026 Tu Soporte Online\n` +
		`#\n` +
		`msgid ""\n` +
		`msgstr ""\n` +
		`"Project-Id-Version: TSO Login Organizer 1.0.0\\n"\n` +
		`"Language: ${ lang }\\n"\n` +
		`"MIME-Version: 1.0\\n"\n` +
		`"Content-Type: text/plain; charset=UTF-8\\n"\n` +
		`"Content-Transfer-Encoding: 8bit\\n"\n` +
		`"Plural-Forms: nplurals=2; plural=(n != 1);\\n"\n` +
		`\n`;

	let body = '';
	for ( const id of unique ) {
		const tr = map[ id ];
		if ( undefined === tr ) {
			process.stderr.write( `Missing ${ lang } translation for: ${ id }\n` );
		}
		body += `msgid "${ esc( id ) }"\nmsgstr "${ esc( tr || '' ) }"\n\n`;
	}
	return header + body;
}

/**
 * Build POT content.
 *
 * @return {string}
 */
function buildPot() {
	let out =
		`# Copyright (C) 2026 Tu Soporte Online\n` +
		`# This file is distributed under the GPLv2 or later.\n` +
		`msgid ""\n` +
		`msgstr ""\n` +
		`"Project-Id-Version: TSO Login Organizer 1.0.0\\n"\n` +
		`"Report-Msgid-Bugs-To: https://www.tusoporteonline.es/blog\\n"\n` +
		`"Language-Team: LANGUAGE <LL@li.org>\\n"\n` +
		`"MIME-Version: 1.0\\n"\n` +
		`"Content-Type: text/plain; charset=UTF-8\\n"\n` +
		`"Content-Transfer-Encoding: 8bit\\n"\n` +
		`\n`;
	for ( const id of unique ) {
		out += `msgid "${ esc( id ) }"\nmsgstr ""\n\n`;
	}
	return out;
}

fs.writeFileSync( path.join( dir, `${ domain }.pot` ), buildPot() );
fs.writeFileSync( path.join( dir, `${ domain }-ca.po` ), buildPo( 'ca', ca ) );
fs.writeFileSync( path.join( dir, `${ domain }-es_ES.po` ), buildPo( 'es_ES', es ) );
process.stdout.write( `Wrote pot/ca/es for ${ unique.length } strings\n` );
