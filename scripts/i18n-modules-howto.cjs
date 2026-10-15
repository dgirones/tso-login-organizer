/**
 * CA/ES for module howto / compatibility / profile UX strings.
 *
 * @package TSO_Login_Organizer
 */
'use strict';

const ca = {
	'How to use': 'Com usar-ho',
	Compatibility: 'Compatibilitat',
	Settings: 'Ajustaments',
	Shortcodes: 'Shortcodes',
	'Open Modules': 'Obrir Mòduls',
	'Required roles': 'Rols obligatoris',
	'Default (all roles)': 'Per defecte (tots els rols)',
	'This module needs these modules enabled first: %s.': 'Aquest mòdul necessita activar abans: %s.',
	'visible only to logged-in users': 'visible només per a usuaris connectats',
	'visible only to guests': 'visible només per a visitants',
	'visible only to the listed roles': 'visible només per als rols indicats',
	'Authorized redirect URIs (paste into each provider console):':
		'URI de redirecció autoritzades (enganxeu-les a la consola de cada proveïdor):',
	'Enable two-factor authentication (TOTP)': 'Activar l’autenticació de dos factors (TOTP)',
	'Add this secret to your authenticator app:': 'Afegiu aquest secret a l’aplicació d’autenticació:',
	'A new secret is created when you save with this box checked.':
		'Es crea un secret nou quan deseu amb aquesta casella marcada.',
	'JPG, PNG, GIF or WebP. Leave empty to keep the current picture.':
		'JPG, PNG, GIF o WebP. Deixeu-ho buit per mantenir la imatge actual.',
	'Favorites module is not available.': 'El mòdul Preferits no està disponible.',
	'Guests are redirected to the login page. Login, register and password URLs stay public.':
		'Els visitants es redirigeixen a l’inici de sessió. Les URL d’accés, registre i contrasenya continuen públiques.',
	'Logged-in users see a button at the end of each single post. The list page uses the Favorites URL slug from Core settings.':
		'Els usuaris connectats veuen un botó al final de cada entrada. La llista usa l’slug de Preferits de Nucli.',
	'Tick roles that must enter a TOTP code after the password. Users enable the authenticator on the frontend profile (Profiles) or in wp-admin → Profile.':
		'Marqueu els rols que han d’introduir un codi TOTP després de la contrasenya. L’usuari activa l’autenticador al perfil frontend (Perfils) o a wp-admin → Perfil.',
	'Requires Profiles. After enabling both: open /profile/, upload under Profile picture. Optional max size on the Avatars tab.':
		'Requiereix Perfils. Després d’activar tots dos: obriu /profile/, pugeu a Imatge de perfil. Mida màxima opcional a la pestanya Avatars.',
	'After enabling: open the Restrictions tab for private site and shortcode examples.':
		'Després d’activar: obriu Restriccions per al lloc privat i exemples de shortcodes.',
	'After enabling: open the Two-Factor tab to choose which roles must use 2FA. Users enable a secret on the frontend profile or in wp-admin.':
		'Després d’activar: obriu Dos factors per triar els rols amb 2FA. L’usuari activa el secret al perfil frontend o a wp-admin.',
	'Enable this module and click Save modules.': 'Activeu aquest mòdul i feu clic a Desa els mòduls.',
	'Open the Redirection tab. Set a default URL and, if needed, a different URL per role for After login, After logout and After registration.':
		'Obriu Redirecció. Definiu una URL per defecte i, si cal, una per rol (després d’accés, sortida i registre).',
	'Leave a field empty to keep the WordPress default for that case.':
		'Deixeu un camp buit per mantenir el comportament per defecte de WordPress.',
	'Other login/redirect plugins (membership, LMS, WooCommerce “redirect after login”) may override these URLs depending on filter priority.':
		'Altres plugins de redirecció (membres, LMS, WooCommerce) poden sobreescriure aquestes URL segons la prioritat del filtre.',
	'Enable this module and Save modules, then open the Moderation tab.':
		'Activeu el mòdul, deseu i obriu Moderació.',
	'Turn on Email confirmation and/or Admin approval, then Save module settings.':
		'Activeu confirmació per correu i/o aprovació d’administrador i deseu.',
	'New registrations receive a confirmation email and/or stay pending until an administrator approves them (Users screen).':
		'Els nous registres reben un correu de confirmació i/o queden pendents fins que un administrador els aprovi (Usuaris).',
	'Turn off Core → Auto login when using moderation, or new users may be signed in before they are approved.':
		'Desactiveu Nucli → Inici de sessió automàtic amb moderació, o els usuaris poden entrar abans d’estar aprovats.',
	'Conflicts with other registration-approval plugins that also block wp_authenticate_user.':
		'Entra en conflicte amb altres plugins d’aprovació de registres que també bloquegen wp_authenticate_user.',
	'Enable this module and Save modules, then open the Restrictions tab.':
		'Activeu el mòdul, deseu i obriu Restriccions.',
	'Optional: tick Private site so guests must log in before seeing any page (login/register URLs stay reachable).':
		'Opcional: marqueu Lloc privat perquè els visitants hagin d’iniciar sessió (les URL d’accés/registre segueixen accessibles).',
	'In posts/pages, wrap content with [tsolo-logged-in]…[/tsolo-logged-in] or [tsolo-logged-out]…[/tsolo-logged-out].':
		'A entrades/pàgines, emboliqueu el contingut amb [tsolo-logged-in]…[/tsolo-logged-in] o [tsolo-logged-out]…[/tsolo-logged-out].',
	'To allow only some roles: [tsolo-role role="subscriber,customer"]…[/tsolo-role].':
		'Per permetre només alguns rols: [tsolo-role role="subscriber,customer"]…[/tsolo-role].',
	'Private site redirects guests site-wide — do not combine with another “force login” / members-only plugin.':
		'Lloc privat redirigeix els visitants a tot el lloc: no ho combineu amb un altre plugin de “forçar login”.',
	'Caching plugins may serve public HTML to guests; exclude restricted pages or disable cache for logged-out users when testing.':
		'Els plugins de cache poden servir HTML públic als visitants; excloeu pàgines restringides o desactiveu la cache en proves.',
	'Enable only if Swiss Knife Login Protect / Kadence Security are not already doing the same job.':
		'Activeu-ho només si Swiss Knife Login Protect / Kadence Security no fan ja el mateix.',
	'Open the Security tab: enable lockouts, set max attempts and lockout minutes, optional min password length.':
		'Obriu Seguretat: activeu bloquejos, intents màxims i minuts, longitud mínima opcional.',
	'Hide wp-login.php returns 404 on the default login file — use only with this plugin’s frontend login URL and no other custom-login plugins.':
		'Amagar wp-login.php retorna 404 al fitxer d’accés per defecte: useu-ho només amb l’URL frontend d’aquest plugin.',
	'May conflict with TSO Swiss Knife Login Protect and Kadence Security (lockouts / hide login).':
		'Pot entrar en conflicte amb TSO Swiss Knife Login Protect i Kadence Security (bloquejos / amagar login).',
	'Hide wp-login.php conflicts with any plugin that renames or protects wp-login.php.':
		'Amagar wp-login.php xoca amb qualsevol plugin que reanomena o protegeix wp-login.php.',
	'Create a reCAPTCHA v2 “I’m not a robot” site at Google reCAPTCHA admin and add your domain.':
		'Creeu un lloc reCAPTCHA v2 «No sóc un robot» a l’admin de Google i afegiu-hi el domini.',
	'Enable this module, paste Site key and Secret key below, Save module settings.':
		'Activeu el mòdul, enganxeu la clau del lloc i la secreta i deseu.',
	'The widget appears on this plugin’s login, register and lost-password forms. Leave keys empty to disable checks.':
		'El giny surt als formularis d’accés, registre i contrasenya oblidada. Deixeu les claus buides per desactivar les comprovacions.',
	'Do not enable together with CleanTalk, Kadence captcha, or another reCAPTCHA on the same forms (double challenges / failed verifies).':
		'No ho activeu juntament amb CleanTalk, captcha de Kadence o un altre reCAPTCHA als mateixos formularis.',
	'Sends visitor IP and token to Google for verification when forms are submitted (see readme External services).':
		'Envia la IP i el token a Google per verificar quan s’envien els formularis (vegeu serveis externs al readme).',
	'Enable this module and Save modules. A virtual profile page is created (default slug /profile/ — change under Core → URL slugs).':
		'Activeu el mòdul i deseu. Es crea una pàgina virtual de perfil (slug per defecte /profile/ — canvieu-lo a Nucli → Slugs).',
	'Logged-in users open %s to edit display name, email and password.':
		'Els usuaris connectats obren %s per editar nom visible, correu i contrasenya.',
	'Optional: Block wp-admin redirects subscribers (no edit_posts) away from /wp-admin/ to the profile page and hides the admin bar.':
		'Opcional: Bloquejar wp-admin redirigeix els subscriptors (sense edit_posts) al perfil i amaga la barra d’admin.',
	'Enable Avatars as well if you want a profile-picture upload on that same page.':
		'Activeu també Avatars si voleu pujar una imatge de perfil a la mateixa pàgina.',
	'Block wp-admin prevents those users from reaching wp-admin screens (including other plugins’ profile tabs). Prefer frontend setup for 2FA when this is on.':
		'Bloquejar wp-admin impedeix arribar a pantalles d’admin (inclosos altres plugins). Preferiu configurar el 2FA al frontend.',
	'Enable this module, open the Notifications tab, edit subjects/bodies, Save module settings.':
		'Activeu el mòdul, obriu Notificacions, editeu assumptes/cossos i deseu.',
	'Use placeholders listed under each field (e.g. {user_login}, {reset_url}). Empty fields keep the WordPress default text.':
		'Useu els marcadors sota cada camp (p. ex. {user_login}, {reset_url}). Els camps buits mantenen el text de WordPress.',
	'HTML emails: tick the option only if your body contains HTML; otherwise leave plain text.':
		'Correus HTML: marqueu l’opció només si el cos té HTML; si no, deixeu text pla.',
	'SMTP / email-template plugins may still wrap or override outgoing mail after this filter runs.':
		'Plugins SMTP / plantilles de correu encara poden embolcallar o sobreescriure el correu després d’aquest filtre.',
	'Enable this module (keep off if another 2FA plugin is active), Save modules, open the Two-Factor tab.':
		'Activeu el mòdul (deixeu-lo apagat si ja hi ha un altre 2FA), deseu i obriu Dos factors.',
	'Tick the roles that must use 2FA, Save module settings.':
		'Marqueu els rols que han d’usar 2FA i deseu.',
	'Each user enables Authenticator on the frontend profile page (Profiles module) or under Users → Profile in wp-admin, then adds the secret to an app (Google Authenticator, etc.).':
		'Cada usuari activa l’autenticador al perfil frontend (Perfils) o a Usuaris → Perfil a wp-admin, i afegeix el secret a una app.',
	'On login they enter the 6-digit code in the Authentication code field.':
		'A l’accés introdueixen el codi de 6 dígits al camp Codi d’autenticació.',
	'Conflicts with other WordPress 2FA / TOTP plugins on the same accounts.':
		'Entra en conflicte amb altres plugins 2FA / TOTP de WordPress als mateixos comptes.',
	'If Profiles → Block wp-admin is on, users should enable 2FA on the frontend profile (not wp-admin).':
		'Si Perfils → Bloquejar wp-admin està actiu, cal activar el 2FA al perfil frontend (no a wp-admin).',
	'Create an OAuth app at each provider. Set the redirect / callback URL to the values shown on the Social Login tab (site home with ?tsolo_social=provider).':
		'Creeu una app OAuth a cada proveïdor. Poseu la URL de callback als valors de la pestanya Inici de sessió social (?tsolo_social=proveïdor).',
	'Enable this module, paste client ID and secret for each provider you want, Leave a provider empty to hide its button.':
		'Activeu el mòdul, enganxeu ID i secret de cada proveïdor. Deixeu-ne un buit per amagar-ne el botó.',
	'Buttons appear under the frontend login and register forms. Users must allow the app; an account is linked or created on success.':
		'Els botons surten sota els formularis d’accés i registre. L’usuari ha d’autoritzar l’app; se li enllaça o crea el compte.',
	'Other social-login plugins on the same forms can show duplicate buttons or fight over callbacks.':
		'Altres plugins de login social als mateixos formularis poden duplicar botons o disputar els callbacks.',
	'Sends users to Google / Facebook / X and exchanges tokens with those APIs (see readme External services). Off until credentials are saved.':
		'Envia els usuaris a Google / Facebook / X i intercanvia tokens (vegeu serveis externs). Inactiu fins a desar credencials.',
	'Enable Profiles and Avatars, then Save modules (Avatars alone has nowhere to upload).':
		'Activeu Perfils i Avatars i deseu (Avatars sol no té on pujar).',
	'Optional: set Max size (KB) below and Save module settings.':
		'Opcional: definiu la mida màxima (KB) a sota i deseu.',
	'While logged in, open the profile page: %s': 'Amb la sessió iniciada, obriu la pàgina de perfil: %s',
	'Choose an image (JPG, PNG, GIF or WebP) under Profile picture and save. The file is stored in uploads/tso-login-organizer/ and replaces Gravatar for that user site-wide.':
		'Trieu una imatge (JPG, PNG, GIF o WebP) a Imatge de perfil i deseu. Es desa a uploads/tso-login-organizer/ i substitueix Gravatar.',
	'Requires the Profiles module.': 'Requereix el mòdul Perfils.',
	'Other avatar plugins that filter get_avatar / get_avatar_url may override or conflict.':
		'Altres plugins d’avatars que filtren get_avatar / get_avatar_url poden sobreescriure o entrar en conflicte.',
	'Enable this module and Save modules. A virtual page is created (default /favorites/ — change under Core → URL slugs).':
		'Activeu el mòdul i deseu. Es crea una pàgina virtual (per defecte /favorites/ — canvieu-la a Nucli → Slugs).',
	'On the Favorites tab, keep “Show on posts” checked to display Add/Remove on single blog posts for logged-in users.':
		'A Preferits, mantingueu «Mostrar a les entrades» per veure Afegir/Treure a les entrades (usuaris connectats).',
	'Users manage the list at %s.': 'Els usuaris gestionen la llista a %s.',
	'Buttons are injected into post content; page builders that replace the_content may hide them — use the favorites page URL as a fallback.':
		'Els botons s’injecten al contingut; constructors de pàgines que substitueixen the_content poden amagar-los — useu la URL de preferits.',
	'Create a Mailchimp API key and copy the Audience (list) ID.':
		'Creeu una clau API de Mailchimp i copieu l’ID d’audiència (llista).',
	'Enable this module, paste API key and List ID, optionally keep “Ask for newsletter consent” on, Save module settings.':
		'Activeu el mòdul, enganxeu clau API i ID de llista, opcionalment demaneu consentiment, i deseu.',
	'On the registration form, users who tick the box are added to that audience after signup. No data is sent until keys are saved and the module is on.':
		'Al registre, qui marqui la casella s’afegeix a l’audiència. No s’envien dades fins a desar claus i activar el mòdul.',
	'Sends subscriber email (and name if available) to Mailchimp’s API (see readme External services).':
		'Envia el correu (i el nom si hi és) a l’API de Mailchimp (vegeu serveis externs al readme).',
};

const es = {
	'How to use': 'Cómo usarlo',
	Compatibility: 'Compatibilidad',
	Settings: 'Ajustes',
	Shortcodes: 'Shortcodes',
	'Open Modules': 'Abrir Módulos',
	'Required roles': 'Roles obligatorios',
	'Default (all roles)': 'Por defecto (todos los roles)',
	'This module needs these modules enabled first: %s.': 'Este módulo necesita activar antes: %s.',
	'visible only to logged-in users': 'visible solo para usuarios conectados',
	'visible only to guests': 'visible solo para visitantes',
	'visible only to the listed roles': 'visible solo para los roles indicados',
	'Authorized redirect URIs (paste into each provider console):':
		'URI de redirección autorizadas (pégalas en la consola de cada proveedor):',
	'Enable two-factor authentication (TOTP)': 'Activar la autenticación de dos factores (TOTP)',
	'Add this secret to your authenticator app:': 'Añade este secreto a la aplicación de autenticación:',
	'A new secret is created when you save with this box checked.':
		'Se crea un secreto nuevo al guardar con esta casilla marcada.',
	'JPG, PNG, GIF or WebP. Leave empty to keep the current picture.':
		'JPG, PNG, GIF o WebP. Déjalo vacío para mantener la imagen actual.',
	'Favorites module is not available.': 'El módulo Favoritos no está disponible.',
	'Guests are redirected to the login page. Login, register and password URLs stay public.':
		'Los visitantes se redirigen al inicio de sesión. Las URL de acceso, registro y contraseña siguen públicas.',
	'Logged-in users see a button at the end of each single post. The list page uses the Favorites URL slug from Core settings.':
		'Los usuarios conectados ven un botón al final de cada entrada. La lista usa el slug de Favoritos de Núcleo.',
	'Tick roles that must enter a TOTP code after the password. Users enable the authenticator on the frontend profile (Profiles) or in wp-admin → Profile.':
		'Marca los roles que deben introducir un código TOTP tras la contraseña. El usuario activa el autenticador en el perfil frontend (Perfiles) o en wp-admin → Perfil.',
	'Requires Profiles. After enabling both: open /profile/, upload under Profile picture. Optional max size on the Avatars tab.':
		'Requiere Perfiles. Tras activar ambos: abre /profile/, sube en Imagen de perfil. Tamaño máximo opcional en la pestaña Avatares.',
	'After enabling: open the Restrictions tab for private site and shortcode examples.':
		'Tras activar: abre Restricciones para el sitio privado y ejemplos de shortcodes.',
	'After enabling: open the Two-Factor tab to choose which roles must use 2FA. Users enable a secret on the frontend profile or in wp-admin.':
		'Tras activar: abre Dos factores para elegir los roles con 2FA. El usuario activa el secreto en el perfil frontend o en wp-admin.',
	'Enable this module and click Save modules.': 'Activa este módulo y pulsa Guardar módulos.',
	'Open the Redirection tab. Set a default URL and, if needed, a different URL per role for After login, After logout and After registration.':
		'Abre Redirección. Define una URL por defecto y, si hace falta, una por rol (tras acceso, salida y registro).',
	'Leave a field empty to keep the WordPress default for that case.':
		'Deja un campo vacío para mantener el comportamiento por defecto de WordPress.',
	'Other login/redirect plugins (membership, LMS, WooCommerce “redirect after login”) may override these URLs depending on filter priority.':
		'Otros plugins de redirección (miembros, LMS, WooCommerce) pueden sobrescribir estas URL según la prioridad del filtro.',
	'Enable this module and Save modules, then open the Moderation tab.':
		'Activa el módulo, guarda y abre Moderación.',
	'Turn on Email confirmation and/or Admin approval, then Save module settings.':
		'Activa confirmación por correo y/o aprobación de administrador y guarda.',
	'New registrations receive a confirmation email and/or stay pending until an administrator approves them (Users screen).':
		'Los nuevos registros reciben un correo de confirmación y/o quedan pendientes hasta que un administrador los apruebe (Usuarios).',
	'Turn off Core → Auto login when using moderation, or new users may be signed in before they are approved.':
		'Desactiva Núcleo → Inicio de sesión automático con moderación, o los usuarios pueden entrar antes de estar aprobados.',
	'Conflicts with other registration-approval plugins that also block wp_authenticate_user.':
		'Choca con otros plugins de aprobación de registros que también bloquean wp_authenticate_user.',
	'Enable this module and Save modules, then open the Restrictions tab.':
		'Activa el módulo, guarda y abre Restricciones.',
	'Optional: tick Private site so guests must log in before seeing any page (login/register URLs stay reachable).':
		'Opcional: marca Sitio privado para que los visitantes deban iniciar sesión (las URL de acceso/registro siguen accesibles).',
	'In posts/pages, wrap content with [tsolo-logged-in]…[/tsolo-logged-in] or [tsolo-logged-out]…[/tsolo-logged-out].':
		'En entradas/páginas, envuelve el contenido con [tsolo-logged-in]…[/tsolo-logged-in] o [tsolo-logged-out]…[/tsolo-logged-out].',
	'To allow only some roles: [tsolo-role role="subscriber,customer"]…[/tsolo-role].':
		'Para permitir solo algunos roles: [tsolo-role role="subscriber,customer"]…[/tsolo-role].',
	'Private site redirects guests site-wide — do not combine with another “force login” / members-only plugin.':
		'Sitio privado redirige a los visitantes en todo el sitio: no lo combines con otro plugin de “forzar login”.',
	'Caching plugins may serve public HTML to guests; exclude restricted pages or disable cache for logged-out users when testing.':
		'Los plugins de caché pueden servir HTML público a visitantes; excluye páginas restringidas o desactiva la caché en pruebas.',
	'Enable only if Swiss Knife Login Protect / Kadence Security are not already doing the same job.':
		'Actívalo solo si Swiss Knife Login Protect / Kadence Security no hacen ya lo mismo.',
	'Open the Security tab: enable lockouts, set max attempts and lockout minutes, optional min password length.':
		'Abre Seguridad: activa bloqueos, intentos máximos y minutos, longitud mínima opcional.',
	'Hide wp-login.php returns 404 on the default login file — use only with this plugin’s frontend login URL and no other custom-login plugins.':
		'Ocultar wp-login.php devuelve 404 en el archivo de acceso por defecto: úsalo solo con la URL frontend de este plugin.',
	'May conflict with TSO Swiss Knife Login Protect and Kadence Security (lockouts / hide login).':
		'Puede chocar con TSO Swiss Knife Login Protect y Kadence Security (bloqueos / ocultar login).',
	'Hide wp-login.php conflicts with any plugin that renames or protects wp-login.php.':
		'Ocultar wp-login.php choca con cualquier plugin que renombre o proteja wp-login.php.',
	'Create a reCAPTCHA v2 “I’m not a robot” site at Google reCAPTCHA admin and add your domain.':
		'Crea un sitio reCAPTCHA v2 «No soy un robot» en el admin de Google y añade el dominio.',
	'Enable this module, paste Site key and Secret key below, Save module settings.':
		'Activa el módulo, pega la clave del sitio y la secreta y guarda.',
	'The widget appears on this plugin’s login, register and lost-password forms. Leave keys empty to disable checks.':
		'El widget aparece en los formularios de acceso, registro y contraseña olvidada. Deja las claves vacías para desactivar las comprobaciones.',
	'Do not enable together with CleanTalk, Kadence captcha, or another reCAPTCHA on the same forms (double challenges / failed verifies).':
		'No lo actives junto con CleanTalk, captcha de Kadence u otro reCAPTCHA en los mismos formularios.',
	'Sends visitor IP and token to Google for verification when forms are submitted (see readme External services).':
		'Envía la IP y el token a Google para verificar al enviar formularios (ver servicios externos en el readme).',
	'Enable this module and Save modules. A virtual profile page is created (default slug /profile/ — change under Core → URL slugs).':
		'Activa el módulo y guarda. Se crea una página virtual de perfil (slug por defecto /profile/ — cámbialo en Núcleo → Slugs).',
	'Logged-in users open %s to edit display name, email and password.':
		'Los usuarios conectados abren %s para editar nombre visible, correo y contraseña.',
	'Optional: Block wp-admin redirects subscribers (no edit_posts) away from /wp-admin/ to the profile page and hides the admin bar.':
		'Opcional: Bloquear wp-admin redirige a los suscriptores (sin edit_posts) al perfil y oculta la barra de admin.',
	'Enable Avatars as well if you want a profile-picture upload on that same page.':
		'Activa también Avatares si quieres subir una imagen de perfil en la misma página.',
	'Block wp-admin prevents those users from reaching wp-admin screens (including other plugins’ profile tabs). Prefer frontend setup for 2FA when this is on.':
		'Bloquear wp-admin impide llegar a pantallas de admin (incluidos otros plugins). Prefiere configurar el 2FA en el frontend.',
	'Enable this module, open the Notifications tab, edit subjects/bodies, Save module settings.':
		'Activa el módulo, abre Notificaciones, edita asuntos/cuerpos y guarda.',
	'Use placeholders listed under each field (e.g. {user_login}, {reset_url}). Empty fields keep the WordPress default text.':
		'Usa los marcadores bajo cada campo (p. ej. {user_login}, {reset_url}). Los campos vacíos mantienen el texto de WordPress.',
	'HTML emails: tick the option only if your body contains HTML; otherwise leave plain text.':
		'Correos HTML: marca la opción solo si el cuerpo tiene HTML; si no, deja texto plano.',
	'SMTP / email-template plugins may still wrap or override outgoing mail after this filter runs.':
		'Plugins SMTP / plantillas de correo aún pueden envolver o sobrescribir el correo tras este filtro.',
	'Enable this module (keep off if another 2FA plugin is active), Save modules, open the Two-Factor tab.':
		'Activa el módulo (déjalo apagado si ya hay otro 2FA), guarda y abre Dos factores.',
	'Tick the roles that must use 2FA, Save module settings.':
		'Marca los roles que deben usar 2FA y guarda.',
	'Each user enables Authenticator on the frontend profile page (Profiles module) or under Users → Profile in wp-admin, then adds the secret to an app (Google Authenticator, etc.).':
		'Cada usuario activa el autenticador en el perfil frontend (Perfiles) o en Usuarios → Perfil en wp-admin, y añade el secreto a una app.',
	'On login they enter the 6-digit code in the Authentication code field.':
		'Al acceder introducen el código de 6 dígitos en el campo Código de autenticación.',
	'Conflicts with other WordPress 2FA / TOTP plugins on the same accounts.':
		'Choca con otros plugins 2FA / TOTP de WordPress en las mismas cuentas.',
	'If Profiles → Block wp-admin is on, users should enable 2FA on the frontend profile (not wp-admin).':
		'Si Perfiles → Bloquear wp-admin está activo, hay que activar el 2FA en el perfil frontend (no en wp-admin).',
	'Create an OAuth app at each provider. Set the redirect / callback URL to the values shown on the Social Login tab (site home with ?tsolo_social=provider).':
		'Crea una app OAuth en cada proveedor. Pon la URL de callback en los valores de la pestaña Inicio de sesión social (?tsolo_social=proveedor).',
	'Enable this module, paste client ID and secret for each provider you want, Leave a provider empty to hide its button.':
		'Activa el módulo, pega ID y secreto de cada proveedor. Deja uno vacío para ocultar su botón.',
	'Buttons appear under the frontend login and register forms. Users must allow the app; an account is linked or created on success.':
		'Los botones aparecen bajo los formularios de acceso y registro. El usuario debe autorizar la app; se vincula o crea la cuenta.',
	'Other social-login plugins on the same forms can show duplicate buttons or fight over callbacks.':
		'Otros plugins de login social en los mismos formularios pueden duplicar botones o disputar los callbacks.',
	'Sends users to Google / Facebook / X and exchanges tokens with those APIs (see readme External services). Off until credentials are saved.':
		'Envía a los usuarios a Google / Facebook / X e intercambia tokens (ver servicios externos). Inactivo hasta guardar credenciales.',
	'Enable Profiles and Avatars, then Save modules (Avatars alone has nowhere to upload).':
		'Activa Perfiles y Avatares y guarda (Avatares solo no tiene dónde subir).',
	'Optional: set Max size (KB) below and Save module settings.':
		'Opcional: define el tamaño máximo (KB) abajo y guarda.',
	'While logged in, open the profile page: %s': 'Con la sesión iniciada, abre la página de perfil: %s',
	'Choose an image (JPG, PNG, GIF or WebP) under Profile picture and save. The file is stored in uploads/tso-login-organizer/ and replaces Gravatar for that user site-wide.':
		'Elige una imagen (JPG, PNG, GIF o WebP) en Imagen de perfil y guarda. Se guarda en uploads/tso-login-organizer/ y sustituye Gravatar.',
	'Requires the Profiles module.': 'Requiere el módulo Perfiles.',
	'Other avatar plugins that filter get_avatar / get_avatar_url may override or conflict.':
		'Otros plugins de avatares que filtran get_avatar / get_avatar_url pueden sobrescribir o chocar.',
	'Enable this module and Save modules. A virtual page is created (default /favorites/ — change under Core → URL slugs).':
		'Activa el módulo y guarda. Se crea una página virtual (por defecto /favorites/ — cámbiala en Núcleo → Slugs).',
	'On the Favorites tab, keep “Show on posts” checked to display Add/Remove on single blog posts for logged-in users.':
		'En Favoritos, mantén «Mostrar en las entradas» para ver Añadir/Quitar en las entradas (usuarios conectados).',
	'Users manage the list at %s.': 'Los usuarios gestionan la lista en %s.',
	'Buttons are injected into post content; page builders that replace the_content may hide them — use the favorites page URL as a fallback.':
		'Los botones se inyectan en el contenido; maquetadores que sustituyen the_content pueden ocultarlos — usa la URL de favoritos.',
	'Create a Mailchimp API key and copy the Audience (list) ID.':
		'Crea una clave API de Mailchimp y copia el ID de audiencia (lista).',
	'Enable this module, paste API key and List ID, optionally keep “Ask for newsletter consent” on, Save module settings.':
		'Activa el módulo, pega clave API e ID de lista, opcionalmente pide consentimiento, y guarda.',
	'On the registration form, users who tick the box are added to that audience after signup. No data is sent until keys are saved and the module is on.':
		'En el registro, quien marque la casilla se añade a la audiencia. No se envían datos hasta guardar claves y activar el módulo.',
	'Sends subscriber email (and name if available) to Mailchimp’s API (see readme External services).':
		'Envía el correo (y el nombre si hay) a la API de Mailchimp (ver servicios externos en el readme).',
};

module.exports = { ca, es };
