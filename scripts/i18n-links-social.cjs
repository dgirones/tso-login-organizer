/**
 * CA/ES for clickable test links + Social Login setup copy.
 *
 * @package TSO_Login_Organizer
 */
'use strict';

const ca = {
	'Open in new tab': 'Obrir en una pestanya nova',
	'Open login page': 'Obrir pàgina d’accés',
	'Open register page': 'Obrir pàgina de registre',
	'Open profile page': 'Obrir pàgina de perfil',
	'Open favorites page': 'Obrir pàgina de preferits',
	'Open Users screen': 'Obrir pantalla d’Usuaris',
	'Open callback URL': 'Obrir URL de callback',
	'Google reCAPTCHA admin': 'Admin de Google reCAPTCHA',
	'Open Google reCAPTCHA admin': 'Obrir admin de Google reCAPTCHA',
	'Mailchimp API keys': 'Claus API de Mailchimp',
	'Open Mailchimp API keys': 'Obrir claus API de Mailchimp',
	'How to find Audience ID': 'Com trobar l’ID d’audiència',
	'Google Cloud credentials': 'Credencials de Google Cloud',
	'Facebook apps': 'Apps de Facebook',
	'X developer portal': 'Portal de desenvolupadors d’X',
	Google: 'Google',
	Facebook: 'Facebook',
	'X (Twitter)': 'X (Twitter)',
	'Open Google Cloud → Credentials': 'Obrir Google Cloud → Credencials',
	'Open Facebook Developers → Apps': 'Obrir Facebook Developers → Apps',
	'Open X Developer Portal': 'Obrir el portal de desenvolupadors d’X',
	'Redirect / callback URI (copy into the provider console):':
		'URI de redirecció / callback (copieu-la a la consola del proveïdor):',
	'Leave a provider empty to hide its button. After saving, test from the login page.':
		'Deixeu un proveïdor buit per amagar-ne el botó. Després de desar, proveu des de la pàgina d’accés.',
	'Follow the provider steps on this tab (each has a direct console link). Copy the redirect URI shown for that provider into the console.':
		'Seguiu els passos de cada proveïdor en aquesta pestanya (cadascun té un enllaç directe). Copieu l’URI de redirecció a la consola.',
	'Paste Client ID / App ID and Secret below for each provider you want. Leave a provider empty to hide its button.':
		'Enganxeu Client ID / App ID i Secret a sota per a cada proveïdor. Deixeu-ne un buit per amagar-ne el botó.',
	'Save module settings, then open the login page and try the social button.':
		'Deseu la configuració del mòdul, obriu la pàgina d’accés i proveu el botó social.',
	'Open Google Cloud Credentials and create (or select) a project.':
		'Obriu Credencials de Google Cloud i creeu (o seleccioneu) un projecte.',
	'Configure the OAuth consent screen (External or Internal) and add your test users if needed.':
		'Configureu la pantalla de consentiment OAuth (External o Internal) i afegiu usuaris de prova si cal.',
	'Create credentials → OAuth client ID → Application type: Web application.':
		'Creeu credencials → ID de client OAuth → Tipus d’aplicació: Aplicació web.',
	'Under Authorized redirect URIs, paste the Google callback URL shown below.':
		'A URI de redirecció autoritzades, enganxeu l’URL de callback de Google que hi ha a sota.',
	'Copy Client ID and Client secret into the fields below, then Save.':
		'Copieu l’ID de client i el secret als camps de sota i deseu.',
	'Create an app (type Consumer / Authenticate and request data from users with Facebook Login).':
		'Creeu una app (tipus Consumer / Authenticate and request data from users with Facebook Login).',
	'Add the Facebook Login product to the app.': 'Afegiu el producte Facebook Login a l’app.',
	'In Facebook Login → Settings, paste the Facebook callback URL below into Valid OAuth Redirect URIs.':
		'A Facebook Login → Settings, enganxeu l’URL de callback de Facebook a Valid OAuth Redirect URIs.',
	'In Settings → Basic, copy App ID and App Secret into the fields below, then Save.':
		'A Settings → Basic, copieu App ID i App Secret als camps de sota i deseu.',
	'Create a Project and App with OAuth 2.0 enabled.':
		'Creeu un Projecte i una App amb OAuth 2.0 activat.',
	'Set App permissions to include users email if you want email on new accounts.':
		'Configureu els permisos de l’app per incloure el correu si voleu email als comptes nous.',
	'Add the X callback URL below as a Callback URI / Redirect URL.':
		'Afegiu l’URL de callback d’X a sota com a Callback URI / Redirect URL.',
	'Copy Client ID and Client Secret into the fields below, then Save.':
		'Copieu Client ID i Client Secret als camps de sota i deseu.',
	'Create a v2 "I\'m not a robot" key, then paste both values here.':
		'Creeu una clau v2 «No sóc un robot» i enganxeu aquí els dos valors.',
	'Open https://www.google.com/recaptcha/admin and create a reCAPTCHA v2 "I\'m not a robot" key for your domain.':
		'Obriu https://www.google.com/recaptcha/admin i creeu una clau reCAPTCHA v2 «No sóc un robot» per al vostre domini.',
	'Create an API key at https://admin.mailchimp.com/account/api/ and copy your Audience (list) ID from Audience → Settings.':
		'Creeu una clau API a https://admin.mailchimp.com/account/api/ i copieu l’ID d’audiència a Audience → Settings.',
};

const es = {
	'Open in new tab': 'Abrir en una pestaña nueva',
	'Open login page': 'Abrir página de acceso',
	'Open register page': 'Abrir página de registro',
	'Open profile page': 'Abrir página de perfil',
	'Open favorites page': 'Abrir página de favoritos',
	'Open Users screen': 'Abrir pantalla de Usuarios',
	'Open callback URL': 'Abrir URL de callback',
	'Google reCAPTCHA admin': 'Admin de Google reCAPTCHA',
	'Open Google reCAPTCHA admin': 'Abrir admin de Google reCAPTCHA',
	'Mailchimp API keys': 'Claves API de Mailchimp',
	'Open Mailchimp API keys': 'Abrir claves API de Mailchimp',
	'How to find Audience ID': 'Cómo encontrar el ID de audiencia',
	'Google Cloud credentials': 'Credenciales de Google Cloud',
	'Facebook apps': 'Apps de Facebook',
	'X developer portal': 'Portal de desarrolladores de X',
	Google: 'Google',
	Facebook: 'Facebook',
	'X (Twitter)': 'X (Twitter)',
	'Open Google Cloud → Credentials': 'Abrir Google Cloud → Credenciales',
	'Open Facebook Developers → Apps': 'Abrir Facebook Developers → Apps',
	'Open X Developer Portal': 'Abrir el portal de desarrolladores de X',
	'Redirect / callback URI (copy into the provider console):':
		'URI de redirección / callback (cópiala en la consola del proveedor):',
	'Leave a provider empty to hide its button. After saving, test from the login page.':
		'Deja un proveedor vacío para ocultar su botón. Tras guardar, prueba desde la página de acceso.',
	'Follow the provider steps on this tab (each has a direct console link). Copy the redirect URI shown for that provider into the console.':
		'Sigue los pasos de cada proveedor en esta pestaña (cada uno tiene un enlace directo). Copia la URI de redirección en la consola.',
	'Paste Client ID / App ID and Secret below for each provider you want. Leave a provider empty to hide its button.':
		'Pega Client ID / App ID y Secret abajo para cada proveedor. Deja uno vacío para ocultar su botón.',
	'Save module settings, then open the login page and try the social button.':
		'Guarda la configuración del módulo, abre la página de acceso y prueba el botón social.',
	'Open Google Cloud Credentials and create (or select) a project.':
		'Abre Credenciales de Google Cloud y crea (o selecciona) un proyecto.',
	'Configure the OAuth consent screen (External or Internal) and add your test users if needed.':
		'Configura la pantalla de consentimiento OAuth (External o Internal) y añade usuarios de prueba si hace falta.',
	'Create credentials → OAuth client ID → Application type: Web application.':
		'Crea credenciales → ID de cliente OAuth → Tipo de aplicación: Aplicación web.',
	'Under Authorized redirect URIs, paste the Google callback URL shown below.':
		'En URI de redirección autorizadas, pega la URL de callback de Google que hay abajo.',
	'Copy Client ID and Client secret into the fields below, then Save.':
		'Copia el ID de cliente y el secreto en los campos de abajo y guarda.',
	'Create an app (type Consumer / Authenticate and request data from users with Facebook Login).':
		'Crea una app (tipo Consumer / Authenticate and request data from users with Facebook Login).',
	'Add the Facebook Login product to the app.': 'Añade el producto Facebook Login a la app.',
	'In Facebook Login → Settings, paste the Facebook callback URL below into Valid OAuth Redirect URIs.':
		'En Facebook Login → Settings, pega la URL de callback de Facebook en Valid OAuth Redirect URIs.',
	'In Settings → Basic, copy App ID and App Secret into the fields below, then Save.':
		'En Settings → Basic, copia App ID y App Secret en los campos de abajo y guarda.',
	'Create a Project and App with OAuth 2.0 enabled.':
		'Crea un Proyecto y una App con OAuth 2.0 activado.',
	'Set App permissions to include users email if you want email on new accounts.':
		'Configura los permisos de la app para incluir el correo si quieres email en las cuentas nuevas.',
	'Add the X callback URL below as a Callback URI / Redirect URL.':
		'Añade la URL de callback de X abajo como Callback URI / Redirect URL.',
	'Copy Client ID and Client Secret into the fields below, then Save.':
		'Copia Client ID y Client Secret en los campos de abajo y guarda.',
	'Create a v2 "I\'m not a robot" key, then paste both values here.':
		'Crea una clave v2 «No soy un robot» y pega aquí ambos valores.',
	'Open https://www.google.com/recaptcha/admin and create a reCAPTCHA v2 "I\'m not a robot" key for your domain.':
		'Abre https://www.google.com/recaptcha/admin y crea una clave reCAPTCHA v2 «No soy un robot» para tu dominio.',
	'Create an API key at https://admin.mailchimp.com/account/api/ and copy your Audience (list) ID from Audience → Settings.':
		'Crea una clave API en https://admin.mailchimp.com/account/api/ y copia el ID de audiencia en Audience → Settings.',
};

module.exports = { ca, es };
