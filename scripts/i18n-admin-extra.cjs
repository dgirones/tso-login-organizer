/**
 * Extra CA/ES strings for admin UI help text.
 *
 * @package TSO_Login_Organizer
 */
'use strict';

const ca = {
	'Plugin sections': 'Seccions del plugin',
	'After login': 'Després d’iniciar sessió',
	'After logout': 'Després de tancar sessió',
	'After registration': 'Després del registre',
	'API key': 'Clau API',
	'Ask for newsletter consent on the registration form': 'Demanar consentiment del butlletí al formulari de registre',
	'Audience / list ID where new subscribers are added.': 'ID de l’audiència / llista on s’afegeixen els nous subscriptors.',
	'Collision risk:': 'Risc de col·lisió:',
	'Configuration:': 'Configuració:',
	'Confirm action': 'Confirmar acció',
	'Confirm privacy request': 'Confirmar sol·licitud de privadesa',
	'Create OAuth apps at each provider and paste the credentials here. Leave a provider empty to hide its button.':
		'Creeu aplicacions OAuth a cada proveïdor i enganxeu aquí les credencials. Deixeu un proveïdor buit per amagar-ne el botó.',
	'Customize welcome and password-reset emails (subject, body, optional HTML).':
		'Personalitzeu els correus de benvinguda i de restabliment de contrasenya (assumpte, cos, HTML opcional).',
	'Do not combine with Swiss Knife custom login URL or other plugins that rename wp-login.php.':
		'No ho combineu amb l’URL d’accés personalitzat de Swiss Knife ni amb altres plugins que canviïn wp-login.php.',
	'Email only creates a username from the email address. Username + email asks for both fields.':
		'Només correu crea el nom d’usuari a partir de l’adreça. Nom d’usuari + correu demana els dos camps.',
	'Facebook app ID': 'ID d’aplicació de Facebook',
	'Facebook app secret': 'Secret d’aplicació de Facebook',
	'Failed tries allowed before a lockout starts.': 'Intents fallits permesos abans de bloquejar.',
	'Frontend login, registration and account pages. Optional modules add redirection, moderation, security and more.':
		'Pàgines frontend d’inici de sessió, registre i compte. Els mòduls opcionals afegeixen redirecció, moderació, seguretat i més.',
	'Frontend profile page so users can edit their account without using wp-admin.':
		'Pàgina de perfil al frontend perquè els usuaris editin el compte sense wp-admin.',
	'Google client ID': 'ID de client de Google',
	'Google client secret': 'Secret de client de Google',
	'Guests are redirected to the login page. You can also wrap content with restriction shortcodes when this module is on.':
		'Els visitants es redirigeixen a la pàgina d’accés. També podeu envoltar contingut amb shortcodes de restricció amb aquest mòdul actiu.',
	'How long the lockout lasts.': 'Quant dura el bloqueig.',
	'If off, WordPress emails a generated password. If on, the register form includes password fields.':
		'Si està desactivat, WordPress envia una contrasenya generada. Si està activat, el formulari de registre inclou camps de contrasenya.',
	'Leave a URL empty to keep WordPress defaults. Role-specific URLs override the default for that role.':
		'Deixeu una URL buida per mantenir els valors per defecte de WordPress. Les URL per rol substitueixen el valor per defecte d’aquell rol.',
	'Let users upload a local profile picture instead of only using Gravatar.':
		'Permet pujar una foto de perfil local en lloc d’usar només Gravatar.',
	'List ID': 'ID de llista',
	'Lock accounts after failed logins, enforce password length, and optionally hide wp-login.php.':
		'Bloqueja després d’inicis fallits, obliga una longitud mínima de contrasenya i opcionalment amaga wp-login.php.',
	'Logged-in users can save favorite posts and view them on a dedicated page.':
		'Els usuaris connectats poden desar entrades preferides i veure-les en una pàgina dedicada.',
	Login: 'Accés',
	Logout: 'Sortida',
	'Lost password': 'Contrasenya perduda',
	'Mailchimp API key from your account. Data is sent only when this module is enabled and a list is set.':
		'Clau API de Mailchimp del vostre compte. Les dades només s’envien amb el mòdul actiu i una llista definida.',
	'Make the site private (login required) and restrict content with shortcodes.':
		'Fes el lloc privat (cal iniciar sessió) i restringeix contingut amb shortcodes.',
	'Maximum upload size for profile pictures stored under uploads/tso-login-organizer/.':
		'Mida màxima de pujada per a fotos de perfil a uploads/tso-login-organizer/.',
	'May overlap with Swiss Knife Login Protect, Kadence Security or CleanTalk. Keep off if those already cover the same job.':
		'Pot solapar-se amb Swiss Knife Login Protect, Kadence Security o CleanTalk. Mantingueu-lo desactivat si ja cobreixen la mateixa funció.',
	'Minimum characters when users choose a password (0 = no extra rule).':
		'Caràcters mínims quan l’usuari tria contrasenya (0 = sense regla extra).',
	'Modules are optional. Enable only what you need. After enabling, open that module tab to configure details.':
		'Els mòduls són opcionals. Activeu només el que necessiteu. Després d’activar-ne un, obriu la seva pestanya per configurar-lo.',
	'New accounts stay pending until an administrator approves them':
		'Els comptes nous resten pendents fins que un administrador els aprovi',
	'Optionally subscribe new registrants to a Mailchimp audience (API key + list ID).':
		'Subscriu opcionalment els nous registres a una audiència de Mailchimp (clau API + ID de llista).',
	'Pretty permalink segments for each form (e.g. /login/). Change carefully; save and visit the new URLs after updating.':
		'Segments d’enllaç permanent de cada formulari (p. ex. /login/). Canvieu-los amb cura; deseu i visiteu les noves URL.',
	'Private key used server-side to verify tokens. Leave empty and keep the module off if CleanTalk (or similar) already protects forms.':
		'Clau privada per verificar tokens al servidor. Deixeu-la buida i el mòdul desactivat si CleanTalk (o similar) ja protegeix els formularis.',
	'Protect login and registration forms with Google reCAPTCHA (needs site and secret keys).':
		'Protegeix els formularis d’accés i registre amb Google reCAPTCHA (calen claus pública i secreta).',
	'Public key from the Google reCAPTCHA admin console.': 'Clau pública de la consola d’administració de Google reCAPTCHA.',
	'Require a TOTP code for the selected roles. Each user must enable an authenticator secret on their profile first.':
		'Exigeix un codi TOTP per als rols seleccionats. Cada usuari ha d’activar abans un secret d’autenticador al perfil.',
	'Require email confirmation and/or admin approval before new accounts can log in.':
		'Exigeix confirmació per correu i/o aprovació d’administrador abans que els comptes nous puguin iniciar sessió.',
	'Require users to confirm their email before they can log in':
		'Exigeix que els usuaris confirmin el correu abans d’iniciar sessió',
	'Reset password': 'Restablir contrasenya',
	'Return 404 on wp-login.php': 'Retornar 404 a wp-login.php',
	'Send notification emails as HTML': 'Enviar els correus de notificació en HTML',
	'Send users to different URLs after login, logout or registration, optionally per role.':
		'Envia els usuaris a URL diferents després d’iniciar sessió, sortir o registrar-se, opcionalment per rol.',
	'Show add/remove favorite controls on single posts': 'Mostrar controls d’afegir/treure preferits a les entrades',
	'Sign in with Google, Facebook or X using OAuth app credentials you provide.':
		'Inicia sessió amb Google, Facebook o X amb les credencials OAuth que proporcioneu.',
	'Skip the "check your email" step and sign the new user in immediately (not recommended with email confirmation / admin approval).':
		'Omet el pas de «consulteu el correu» i inicia sessió de seguida (no recomanat amb confirmació / aprovació).',
	'Subject line for the welcome / new-account email.': 'Assumpte del correu de benvinguda / compte nou.',
	'Submit login and related forms without a full page reload. Disable if another plugin already handles form AJAX.':
		'Envia els formularis d’accés sense recarregar tota la pàgina. Desactiveu-ho si un altre plugin ja gestiona l’AJAX.',
	'Subscribers and similar roles cannot open /wp-admin/; they use the frontend profile instead.':
		'Els subscriptors i rols similars no poden obrir /wp-admin/; usen el perfil del frontend.',
	'Temporarily block logins from an IP after too many failed attempts.':
		'Bloqueja temporalment els inicis de sessió d’una IP després de massa intents fallits.',
	'TOTP authenticator codes for selected roles after password login.':
		'Codis TOTP d’autenticador per a rols seleccionats després de la contrasenya.',
	'URL slugs': 'Slugs d’URL',
	'What visitors may type in the login field: either username, email, or both.':
		'El que poden escriure els visitants al camp d’accés: nom d’usuari, correu o tots dos.',
	'WordPress setting: show the registration form and allow new accounts. Same as Settings > General.':
		'Ajustament de WordPress: mostra el formulari de registre i permet comptes nous. Igual que Arranjament > General.',
	'X (Twitter) client ID': 'ID de client d’X (Twitter)',
	'X (Twitter) client secret': 'Secret de client d’X (Twitter)',
	'How to use modules': 'Com usar els mòduls',
	'Tick Enable on the features you want, then click Save modules.': 'Marqueu Activa a les funcions que vulgueu i feu clic a Desa els mòduls.',
	'A new tab with the module name appears at the top of this page. Open it to configure options (keys, URLs, checkboxes, etc.).':
		'Apareix una pestanya nova amb el nom del mòdul a dalt de la pàgina. Obriu-la per configurar opcions (claus, URL, caselles, etc.).',
	'Until a module is enabled, it does nothing and has no settings tab. Disable it again anytime to turn that feature off.':
		'Fins que no activeu un mòdul, no fa res i no té pestanya d’ajustaments. El podeu desactivar en qualsevol moment.',
	'Open "%s" settings tab': 'Obrir la pestanya d’ajustaments «%s»',
	'These values are the path after your site URL for each virtual form. With the default "login" slug, the form is at:':
		'Aquests valors són el camí després de l’URL del lloc per a cada formulari virtual. Amb l’slug per defecte «login», el formulari és a:',
	'Settings > Permalinks must not be "Plain" (use Post name or similar).':
		'Arranjament > Enllaços permanents no pot ser «Simple» (useu Nom de l’entrada o similar).',
	'Edit a slug below (letters, numbers, hyphens only), then click Save core settings (rewrites are flushed automatically).':
		'Editeu un slug a sota (només lletres, números i guions) i feu clic a Desa la configuració del nucli (les regles es regeneren soles).',
	'Open the new URL in the browser (e.g. /accedir/ if you changed login to accedir). Update menus/links that pointed to the old path.':
		'Obriu la nova URL al navegador (p. ex. /accedir/ si heu canviat login per accedir). Actualitzeu menús/enllaços de l’antic camí.',
	'Do not create WordPress pages for these slugs — they are created virtually. You can delete Theme My Login pages with the same names. Profile and Favorites rewrite only when those modules are enabled.':
		'No creeu pàgines de WordPress per a aquests slugs: es creen virtualment. Podeu eliminar les pàgines de Theme My Login amb els mateixos noms. Perfil i Preferits només tenen rewrite amb aquests mòduls actius.',
	'Virtual pages': 'Pàgines virtuals',
	'TSO Login Organizer creates login, logout, register, lost password, reset password, dashboard, confirm-action, and (when modules are on) profile and favorites as virtual URLs via rewrite rules. You do not create WordPress pages for them, and you do not need to keep Theme My Login pages.':
		'TSO Login Organizer crea login, logout, registre, contrasenya perduda, restabliment, tauler, confirm-action i (amb mòduls actius) perfil i preferits com a URL virtuals amb regles de rewrite. No cal crear-ne pàgines de WordPress ni conservar les de Theme My Login.',
	'%d WordPress page looks like a Theme My Login / login form page (matching slug, TML option ID, or [theme-my-login] shortcode). You can move it to Trash — the plugin keeps serving the virtual URL.':
		'%d pàgina de WordPress sembla de Theme My Login / formulari d’accés (slug, ID a opcions TML o shortcode [theme-my-login]). La podeu enviar a la paperera: el plugin continua servint la URL virtual.',
	'%d WordPress pages look like Theme My Login / login form pages (matching slug, TML option ID, or [theme-my-login] shortcode). You can move them to Trash — the plugin keeps serving the virtual URLs.':
		'%d pàgines de WordPress semblen de Theme My Login / formularis d’accés (slug, ID a opcions TML o shortcode [theme-my-login]). Les podeu enviar a la paperera: el plugin continua servint les URL virtuals.',
	'No leftover login pages found (by slug, TML options, or shortcode). Everything is served virtually.':
		'No s’han trobat pàgines d’accés sobrants (per slug, opcions TML o shortcode). Tot es serveix virtualment.',
	slug: 'slug',
	'TML option': 'opció TML',
	shortcode: 'shortcode',
	'After import: trash the old Theme My Login WordPress pages, keep pretty permalinks, then open each virtual URL from Core → URL slugs to confirm forms load.':
		'Després de la importació: envieu a la paperera les pàgines antigues de Theme My Login, mantingueu enllaços permanents amigables i obriu cada URL virtual des de Nucli → Slugs d’URL per confirmar els formularis.',
	'Virtual sign-in URL (example: yoursite.com/login/). No WordPress page needed.':
		'URL virtual d’inici de sessió (exemple: elteulloc.com/login/). No cal pàgina de WordPress.',
	'Virtual logout URL (menus and links). No WordPress page needed.':
		'URL virtual de tancament de sessió (menús i enllaços). No cal pàgina de WordPress.',
	'Virtual registration URL (only if "Anyone can register" is on).':
		'URL virtual de registre (només si «Qualsevol pot registrar-se» està actiu).',
	'Virtual forgot-password URL.': 'URL virtual de contrasenya oblidada.',
	'Virtual page opened from the reset link in the email.': 'Pàgina virtual que s’obre des de l’enllaç de restabliment del correu.',
	'Virtual account dashboard for logged-in users.': 'Tauler de compte virtual per a usuaris connectats.',
	'Virtual confirm URL for personal-data export/erase requests (WordPress privacy tools).':
		'URL virtual de confirmació per a sol·licituds d’exportació/esborrat de dades personals (eines de privadesa de WordPress).',
	'Virtual frontend profile URL (requires Profiles module).': 'URL virtual de perfil frontend (cal el mòdul Perfils).',
	'Virtual favorites URL (requires Favorites module).': 'URL virtual de preferits (cal el mòdul Preferits).',
	Action: 'Acció',
	Delivery: 'Entrega',
	'Virtual (active)': 'Virtual (activa)',
	'Virtual (enable module)': 'Virtual (activeu el mòdul)',
	Slug: 'Slug',
	'URL preview': 'Vista prèvia de l’URL',
	'Modules updated.': 'Mòduls actualitzats.',
	'Modules updated. You enabled a module that may conflict with Swiss Knife, Kadence Security or CleanTalk — keep overlapping features off elsewhere.':
		'Mòduls actualitzats. Heu activat un mòdul que pot entrar en conflicte amb Swiss Knife, Kadence Security o CleanTalk: desactiveu les funcions solapades a l’altre lloc.',
	'After enabling: open the Redirection tab to set default and per-role URLs for login, logout and registration.':
		'Després d’activar: obriu la pestanya Redirecció per definir URL per defecte i per rol (accés, sortida i registre).',
	'After enabling: open the Moderation tab to turn email confirmation and/or admin approval on or off.':
		'Després d’activar: obriu Moderació per activar/desactivar confirmació per correu i/o aprovació d’administrador.',
	'After enabling: open the Restrictions tab for private site. Use shortcodes in content to limit who can see a block.':
		'Després d’activar: obriu Restriccions per al lloc privat. Useu shortcodes al contingut per limitar qui pot veure un bloc.',
	'After enabling: open the Security tab for lockout attempts, duration, min password length and hide wp-login.php.':
		'Després d’activar: obriu Seguretat per als intents de bloqueig, durada, longitud mínima de contrasenya i amagar wp-login.php.',
	'After enabling: open the reCAPTCHA tab and paste your Google site key and secret key.':
		'Després d’activar: obriu reCAPTCHA i enganxeu la clau del lloc i la secreta de Google.',
	'After enabling: a /profile/ page is available. Open the Profiles tab to block wp-admin for users without edit_posts.':
		'Després d’activar: hi ha /profile/. Obriu Perfils per bloquejar wp-admin als usuaris sense edit_posts.',
	'After enabling: open the Notifications tab to edit email subjects, bodies and HTML mode.':
		'Després d’activar: obriu Notificacions per editar assumptes, cossos i mode HTML dels correus.',
	'After enabling: open the Two-Factor tab to choose which roles must use 2FA. Users still set a secret in their profile.':
		'Després d’activar: obriu Dos factors per triar quins rols han d’usar 2FA. L’usuari encara defineix el secret al perfil.',
	'After enabling: open the Social Login tab and fill Google / Facebook / X client IDs and secrets.':
		'Després d’activar: obriu Inici de sessió social i ompliu IDs i secrets de Google / Facebook / X.',
	'After enabling: open the Avatars tab to set the max upload size (KB). Upload appears on the profile form.':
		'Després d’activar: obriu Avatars per definir la mida màxima (KB). La pujada surt al formulari de perfil.',
	'After enabling: a /favorites/ page is available. Open the Favorites tab to show controls on single posts.':
		'Després d’activar: hi ha /favorites/. Obriu Preferits per mostrar controls a les entrades.',
	'After enabling: open the Mailchimp tab for API key, list ID and the registration consent checkbox.':
		'Després d’activar: obriu Mailchimp per a la clau API, ID de llista i la casella de consentiment al registre.',
};

const es = {
	'Plugin sections': 'Secciones del plugin',
	'After login': 'Después de iniciar sesión',
	'After logout': 'Después de cerrar sesión',
	'After registration': 'Después del registro',
	'API key': 'Clave API',
	'Ask for newsletter consent on the registration form': 'Pedir consentimiento del boletín en el formulario de registro',
	'Audience / list ID where new subscribers are added.': 'ID de audiencia / lista donde se añaden los nuevos suscriptores.',
	'Collision risk:': 'Riesgo de colisión:',
	'Configuration:': 'Configuración:',
	'Confirm action': 'Confirmar acción',
	'Confirm privacy request': 'Confirmar solicitud de privacidad',
	'Create OAuth apps at each provider and paste the credentials here. Leave a provider empty to hide its button.':
		'Crea apps OAuth en cada proveedor y pega aquí las credenciales. Deja un proveedor vacío para ocultar su botón.',
	'Customize welcome and password-reset emails (subject, body, optional HTML).':
		'Personaliza los correos de bienvenida y de restablecimiento de contraseña (asunto, cuerpo, HTML opcional).',
	'Do not combine with Swiss Knife custom login URL or other plugins that rename wp-login.php.':
		'No lo combines con la URL de acceso personalizada de Swiss Knife ni con otros plugins que renombren wp-login.php.',
	'Email only creates a username from the email address. Username + email asks for both fields.':
		'Solo correo crea el nombre de usuario a partir de la dirección. Nombre de usuario + correo pide ambos campos.',
	'Facebook app ID': 'ID de aplicación de Facebook',
	'Facebook app secret': 'Secreto de aplicación de Facebook',
	'Failed tries allowed before a lockout starts.': 'Intentos fallidos permitidos antes del bloqueo.',
	'Frontend login, registration and account pages. Optional modules add redirection, moderation, security and more.':
		'Páginas frontend de inicio de sesión, registro y cuenta. Los módulos opcionales añaden redirección, moderación, seguridad y más.',
	'Frontend profile page so users can edit their account without using wp-admin.':
		'Página de perfil en el frontend para que los usuarios editen su cuenta sin wp-admin.',
	'Google client ID': 'ID de cliente de Google',
	'Google client secret': 'Secreto de cliente de Google',
	'Guests are redirected to the login page. You can also wrap content with restriction shortcodes when this module is on.':
		'Los visitantes se redirigen a la página de acceso. También puedes envolver contenido con shortcodes de restricción con este módulo activo.',
	'How long the lockout lasts.': 'Cuánto dura el bloqueo.',
	'If off, WordPress emails a generated password. If on, the register form includes password fields.':
		'Si está desactivado, WordPress envía una contraseña generada. Si está activado, el formulario de registro incluye campos de contraseña.',
	'Leave a URL empty to keep WordPress defaults. Role-specific URLs override the default for that role.':
		'Deja una URL vacía para mantener los valores por defecto de WordPress. Las URL por rol sustituyen el valor por defecto de ese rol.',
	'Let users upload a local profile picture instead of only using Gravatar.':
		'Permite subir una foto de perfil local en lugar de usar solo Gravatar.',
	'List ID': 'ID de lista',
	'Lock accounts after failed logins, enforce password length, and optionally hide wp-login.php.':
		'Bloquea tras inicios fallidos, exige longitud mínima de contraseña y opcionalmente oculta wp-login.php.',
	'Logged-in users can save favorite posts and view them on a dedicated page.':
		'Los usuarios conectados pueden guardar entradas favoritas y verlas en una página dedicada.',
	Login: 'Acceso',
	Logout: 'Salida',
	'Lost password': 'Contraseña perdida',
	'Mailchimp API key from your account. Data is sent only when this module is enabled and a list is set.':
		'Clave API de Mailchimp de tu cuenta. Los datos solo se envían con el módulo activo y una lista definida.',
	'Make the site private (login required) and restrict content with shortcodes.':
		'Haz el sitio privado (hace falta iniciar sesión) y restringe contenido con shortcodes.',
	'Maximum upload size for profile pictures stored under uploads/tso-login-organizer/.':
		'Tamaño máximo de subida para fotos de perfil en uploads/tso-login-organizer/.',
	'May overlap with Swiss Knife Login Protect, Kadence Security or CleanTalk. Keep off if those already cover the same job.':
		'Puede solaparse con Swiss Knife Login Protect, Kadence Security o CleanTalk. Manténlo desactivado si ya cubren la misma función.',
	'Minimum characters when users choose a password (0 = no extra rule).':
		'Caracteres mínimos cuando el usuario elige contraseña (0 = sin regla extra).',
	'Modules are optional. Enable only what you need. After enabling, open that module tab to configure details.':
		'Los módulos son opcionales. Activa solo lo que necesites. Tras activar uno, abre su pestaña para configurarlo.',
	'New accounts stay pending until an administrator approves them':
		'Las cuentas nuevas quedan pendientes hasta que un administrador las apruebe',
	'Optionally subscribe new registrants to a Mailchimp audience (API key + list ID).':
		'Suscribe opcionalmente a los nuevos registros a una audiencia de Mailchimp (clave API + ID de lista).',
	'Pretty permalink segments for each form (e.g. /login/). Change carefully; save and visit the new URLs after updating.':
		'Segmentos de enlace permanente de cada formulario (p. ej. /login/). Cámbialos con cuidado; guarda y visita las nuevas URL.',
	'Private key used server-side to verify tokens. Leave empty and keep the module off if CleanTalk (or similar) already protects forms.':
		'Clave privada para verificar tokens en el servidor. Déjala vacía y el módulo desactivado si CleanTalk (o similar) ya protege los formularios.',
	'Protect login and registration forms with Google reCAPTCHA (needs site and secret keys).':
		'Protege los formularios de acceso y registro con Google reCAPTCHA (necesita claves pública y secreta).',
	'Public key from the Google reCAPTCHA admin console.': 'Clave pública de la consola de administración de Google reCAPTCHA.',
	'Require a TOTP code for the selected roles. Each user must enable an authenticator secret on their profile first.':
		'Exige un código TOTP para los roles seleccionados. Cada usuario debe activar antes un secreto de autenticador en su perfil.',
	'Require email confirmation and/or admin approval before new accounts can log in.':
		'Exige confirmación por correo y/o aprobación de administrador antes de que las cuentas nuevas puedan iniciar sesión.',
	'Require users to confirm their email before they can log in':
		'Exige que los usuarios confirmen su correo antes de iniciar sesión',
	'Reset password': 'Restablecer contraseña',
	'Return 404 on wp-login.php': 'Devolver 404 en wp-login.php',
	'Send notification emails as HTML': 'Enviar los correos de notificación en HTML',
	'Send users to different URLs after login, logout or registration, optionally per role.':
		'Envía a los usuarios a URL distintas tras iniciar sesión, salir o registrarse, opcionalmente por rol.',
	'Show add/remove favorite controls on single posts': 'Mostrar controles de añadir/quitar favoritos en las entradas',
	'Sign in with Google, Facebook or X using OAuth app credentials you provide.':
		'Inicia sesión con Google, Facebook o X con las credenciales OAuth que indiques.',
	'Skip the "check your email" step and sign the new user in immediately (not recommended with email confirmation / admin approval).':
		'Omite el paso de «revisa tu correo» e inicia sesión de inmediato (no recomendado con confirmación / aprobación).',
	'Subject line for the welcome / new-account email.': 'Asunto del correo de bienvenida / cuenta nueva.',
	'Submit login and related forms without a full page reload. Disable if another plugin already handles form AJAX.':
		'Envía los formularios de acceso sin recargar toda la página. Desactívalo si otro plugin ya gestiona el AJAX.',
	'Subscribers and similar roles cannot open /wp-admin/; they use the frontend profile instead.':
		'Los suscriptores y roles similares no pueden abrir /wp-admin/; usan el perfil del frontend.',
	'Temporarily block logins from an IP after too many failed attempts.':
		'Bloquea temporalmente los inicios de sesión de una IP tras demasiados intentos fallidos.',
	'TOTP authenticator codes for selected roles after password login.':
		'Códigos TOTP de autenticador para roles seleccionados tras la contraseña.',
	'URL slugs': 'Slugs de URL',
	'What visitors may type in the login field: either username, email, or both.':
		'Lo que pueden escribir los visitantes en el campo de acceso: nombre de usuario, correo o ambos.',
	'WordPress setting: show the registration form and allow new accounts. Same as Settings > General.':
		'Ajuste de WordPress: muestra el formulario de registro y permite cuentas nuevas. Igual que Ajustes > Generales.',
	'X (Twitter) client ID': 'ID de cliente de X (Twitter)',
	'X (Twitter) client secret': 'Secreto de cliente de X (Twitter)',
	'How to use modules': 'Cómo usar los módulos',
	'Tick Enable on the features you want, then click Save modules.': 'Marca Activar en las funciones que quieras y pulsa Guardar módulos.',
	'A new tab with the module name appears at the top of this page. Open it to configure options (keys, URLs, checkboxes, etc.).':
		'Aparece una pestaña nueva con el nombre del módulo arriba de la página. Ábrela para configurar opciones (claves, URL, casillas, etc.).',
	'Until a module is enabled, it does nothing and has no settings tab. Disable it again anytime to turn that feature off.':
		'Hasta que no actives un módulo, no hace nada y no tiene pestaña de ajustes. Puedes desactivarlo en cualquier momento.',
	'Open "%s" settings tab': 'Abrir la pestaña de ajustes «%s»',
	'These values are the path after your site URL for each virtual form. With the default "login" slug, the form is at:':
		'Estos valores son la ruta tras la URL del sitio para cada formulario virtual. Con el slug por defecto «login», el formulario está en:',
	'Settings > Permalinks must not be "Plain" (use Post name or similar).':
		'Ajustes > Enlaces permanentes no puede ser «Simple» (usa Nombre de la entrada o similar).',
	'Edit a slug below (letters, numbers, hyphens only), then click Save core settings (rewrites are flushed automatically).':
		'Edita un slug abajo (solo letras, números y guiones) y pulsa Guardar configuración del núcleo (las reglas se regeneran solas).',
	'Open the new URL in the browser (e.g. /accedir/ if you changed login to accedir). Update menus/links that pointed to the old path.':
		'Abre la nueva URL en el navegador (p. ej. /accedir/ si cambiaste login por accedir). Actualiza menús/enlaces de la ruta antigua.',
	'Do not create WordPress pages for these slugs — they are created virtually. You can delete Theme My Login pages with the same names. Profile and Favorites rewrite only when those modules are enabled.':
		'No crees páginas de WordPress para estos slugs: se crean virtualmente. Puedes eliminar las páginas de Theme My Login con los mismos nombres. Perfil y Favoritos solo tienen rewrite con esos módulos activos.',
	'Virtual pages': 'Páginas virtuales',
	'TSO Login Organizer creates login, logout, register, lost password, reset password, dashboard, confirm-action, and (when modules are on) profile and favorites as virtual URLs via rewrite rules. You do not create WordPress pages for them, and you do not need to keep Theme My Login pages.':
		'TSO Login Organizer crea login, logout, registro, contraseña perdida, restablecimiento, escritorio, confirm-action y (con módulos activos) perfil y favoritos como URL virtuales con reglas de rewrite. No hace falta crear páginas de WordPress ni conservar las de Theme My Login.',
	'%d WordPress page looks like a Theme My Login / login form page (matching slug, TML option ID, or [theme-my-login] shortcode). You can move it to Trash — the plugin keeps serving the virtual URL.':
		'%d página de WordPress parece de Theme My Login / formulario de acceso (slug, ID en opciones TML o shortcode [theme-my-login]). Puedes enviarla a la papelera: el plugin sigue sirviendo la URL virtual.',
	'%d WordPress pages look like Theme My Login / login form pages (matching slug, TML option ID, or [theme-my-login] shortcode). You can move them to Trash — the plugin keeps serving the virtual URLs.':
		'%d páginas de WordPress parecen de Theme My Login / formularios de acceso (slug, ID en opciones TML o shortcode [theme-my-login]). Puedes enviarlas a la papelera: el plugin sigue sirviendo las URL virtuales.',
	'No leftover login pages found (by slug, TML options, or shortcode). Everything is served virtually.':
		'No se han encontrado páginas de acceso sobrantes (por slug, opciones TML o shortcode). Todo se sirve virtualmente.',
	slug: 'slug',
	'TML option': 'opción TML',
	shortcode: 'shortcode',
	'After import: trash the old Theme My Login WordPress pages, keep pretty permalinks, then open each virtual URL from Core → URL slugs to confirm forms load.':
		'Tras la importación: envía a la papelera las páginas antiguas de Theme My Login, mantén enlaces permanentes amigables y abre cada URL virtual desde Núcleo → Slugs de URL para confirmar los formularios.',
	'Virtual sign-in URL (example: yoursite.com/login/). No WordPress page needed.':
		'URL virtual de inicio de sesión (ejemplo: tusitio.com/login/). No hace falta página de WordPress.',
	'Virtual logout URL (menus and links). No WordPress page needed.':
		'URL virtual de cierre de sesión (menús y enlaces). No hace falta página de WordPress.',
	'Virtual registration URL (only if "Anyone can register" is on).':
		'URL virtual de registro (solo si «Cualquiera puede registrarse» está activo).',
	'Virtual forgot-password URL.': 'URL virtual de contraseña olvidada.',
	'Virtual page opened from the reset link in the email.': 'Página virtual que se abre desde el enlace de restablecimiento del correo.',
	'Virtual account dashboard for logged-in users.': 'Escritorio de cuenta virtual para usuarios conectados.',
	'Virtual confirm URL for personal-data export/erase requests (WordPress privacy tools).':
		'URL virtual de confirmación para solicitudes de exportación/borrado de datos personales (herramientas de privacidad de WordPress).',
	'Virtual frontend profile URL (requires Profiles module).': 'URL virtual de perfil frontend (requiere el módulo Perfiles).',
	'Virtual favorites URL (requires Favorites module).': 'URL virtual de favoritos (requiere el módulo Favoritos).',
	Action: 'Acción',
	Delivery: 'Entrega',
	'Virtual (active)': 'Virtual (activa)',
	'Virtual (enable module)': 'Virtual (activa el módulo)',
	Slug: 'Slug',
	'URL preview': 'Vista previa de la URL',
	'Modules updated.': 'Módulos actualizados.',
	'Modules updated. You enabled a module that may conflict with Swiss Knife, Kadence Security or CleanTalk — keep overlapping features off elsewhere.':
		'Módulos actualizados. Has activado un módulo que puede entrar en conflicto con Swiss Knife, Kadence Security o CleanTalk: desactiva las funciones solapadas en el otro sitio.',
	'After enabling: open the Redirection tab to set default and per-role URLs for login, logout and registration.':
		'Tras activar: abre la pestaña Redirección para definir URL por defecto y por rol (acceso, salida y registro).',
	'After enabling: open the Moderation tab to turn email confirmation and/or admin approval on or off.':
		'Tras activar: abre Moderación para activar/desactivar confirmación por correo y/o aprobación de administrador.',
	'After enabling: open the Restrictions tab for private site. Use shortcodes in content to limit who can see a block.':
		'Tras activar: abre Restricciones para el sitio privado. Usa shortcodes en el contenido para limitar quién ve un bloque.',
	'After enabling: open the Security tab for lockout attempts, duration, min password length and hide wp-login.php.':
		'Tras activar: abre Seguridad para intentos de bloqueo, duración, longitud mínima de contraseña y ocultar wp-login.php.',
	'After enabling: open the reCAPTCHA tab and paste your Google site key and secret key.':
		'Tras activar: abre reCAPTCHA y pega la clave del sitio y la secreta de Google.',
	'After enabling: a /profile/ page is available. Open the Profiles tab to block wp-admin for users without edit_posts.':
		'Tras activar: hay /profile/. Abre Perfiles para bloquear wp-admin a usuarios sin edit_posts.',
	'After enabling: open the Notifications tab to edit email subjects, bodies and HTML mode.':
		'Tras activar: abre Notificaciones para editar asuntos, cuerpos y modo HTML de los correos.',
	'After enabling: open the Two-Factor tab to choose which roles must use 2FA. Users still set a secret in their profile.':
		'Tras activar: abre Dos factores para elegir qué roles deben usar 2FA. El usuario aún define el secreto en su perfil.',
	'After enabling: open the Social Login tab and fill Google / Facebook / X client IDs and secrets.':
		'Tras activar: abre Inicio de sesión social y rellena IDs y secretos de Google / Facebook / X.',
	'After enabling: open the Avatars tab to set the max upload size (KB). Upload appears on the profile form.':
		'Tras activar: abre Avatares para definir el tamaño máximo (KB). La subida aparece en el formulario de perfil.',
	'After enabling: a /favorites/ page is available. Open the Favorites tab to show controls on single posts.':
		'Tras activar: hay /favorites/. Abre Favoritos para mostrar controles en las entradas.',
	'After enabling: open the Mailchimp tab for API key, list ID and the registration consent checkbox.':
		'Tras activar: abre Mailchimp para la clave API, ID de lista y la casilla de consentimiento en el registro.',
};

module.exports = { ca, es };
