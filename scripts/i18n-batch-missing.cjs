/**
 * CA/ES for strings extracted after admin/howto packs (profile front + admin help).
 *
 * @package TSO_Login_Organizer
 */
'use strict';

const ca = {
	Account: 'Compte',
	'Admin approval leaves the account pending on the Users screen until an administrator approves it.':
		'L’aprovació de l’administrador deixa el compte pendent a Usuaris fins que un administrador l’aprovi.',
	'After a successful registration the new person is signed in immediately. If Moderation has email confirmation or admin approval turned on, this box is ignored and they stay logged out until that step is finished.':
		'Després d’un registre correcte la persona nova inicia sessió de seguida. Si Moderació té confirmació per correu o aprovació activa, aquesta casella s’ignora i roman desconnectada fins que acabi aquell pas.',
	'After that, login asks for the password and for the 6-digit code shown in the app.':
		'Després, l’inici de sessió demana la contrasenya i el codi de 6 dígits de l’aplicació.',
	'Also adds a captcha or anti-spam check on forms. Two checks on the same form often fail verification.':
		'També afegeix captcha o antispam als formularis. Dues comprovacions al mateix formulari solen fallar.',
	'Also adds social login buttons or OAuth callbacks. Buttons can be duplicated or the return URL can be stolen by the other plugin.':
		'També afegeix botons d’accés social o retorns OAuth. Els botons es poden duplicar o l’altra extensió pot capturar l’URL de retorn.',
	'Also asks for a second code at login. Two authenticators on the same account block sign-in.':
		'També demana un segon codi a l’entrada. Dos autenticadors al mateix compte bloquegen l’accés.',
	'Also blocks login until a registration is approved (wp_authenticate_user). Keep only one approval plugin active.':
		'També bloqueja l’entrada fins a aprovar el registre (wp_authenticate_user). Deixeu només un plugin d’aprovació actiu.',
	'Also blocks wp-admin or replaces the profile screen. Subscribers may be unable to reach either profile.':
		'També bloqueja wp-admin o substitueix la pantalla de perfil. Els subscriptors poden no arribar a cap perfil.',
	'Also forces login or restricts content. Guests may be redirected twice or locked out of pages.':
		'També força l’entrada o restringeix contingut. Els visitants poden redirigir-se dues vegades o quedar fora de pàgines.',
	'Also locks out failed logins or hides wp-login.php. Running both can block legitimate users or hide the login URL twice.':
		'També bloqueja inicis fallits o amaga wp-login.php. Tots dos junts poden bloquejar usuaris vàlids o amagar l’URL d’accés dues vegades.',
	'Also replaces outgoing email content. The message saved here may be wrapped or discarded.':
		'També substitueix el contingut dels correus sortints. El missatge desat aquí pot embolcallar-se o descartar-se.',
	'Also replaces the profile photo (get_avatar). One of the two pictures will win and the other upload may look ignored.':
		'També substitueix la foto de perfil (get_avatar). Una de les dues imatges guanya i l’altra pujada pot semblar ignorada.',
	'Also sends new users to Mailchimp. The same person can be subscribed twice.':
		'També envia usuaris nous a Mailchimp. La mateixa persona pot subscriure’s dues vegades.',
	'Also sends users somewhere after login, logout or registration. The last plugin to run decides the final URL.':
		'També envia usuaris després d’entrar, sortir o registrar-se. L’últim plugin decideix l’URL final.',
	'Also stores favorite posts. The buttons and the list page may disagree about what is saved.':
		'També desa entrades preferides. Els botons i la pàgina de llista poden no coincidir.',
	'Avatars needs Profiles. The upload field is on the profile page, not on this screen. Max size (KB) below is the only setting here.':
		'Avatars necessita Perfils. El camp de pujada és a la pàgina de perfil, no aquí. La mida màxima (KB) és l’únic ajust d’aquesta pantalla.',
	'Block wp-admin sends users who cannot edit posts away from /wp-admin/ to that profile page and hides the admin bar.':
		'Bloquejar wp-admin envia els usuaris sense edit_posts fora de /wp-admin/ a aquella pàgina de perfil i amaga la barra d’administració.',
	'Check friendly URLs': 'Comprova les URL amigables',
	'Check the box and update the profile to turn two-factor on. A key is created only then. The user must add that key to an authenticator app. The next login will ask for the password and for the 6-digit code from the app. Leaving the box unchecked changes nothing.':
		'Marqueu la casella i actualitzeu el perfil per activar el doble factor. La clau es crea només llavors. L’usuari l’ha d’afegir a una app d’autenticació. El proper inici demanarà la contrasenya i el codi de 6 dígits. Si deixeu la casella sense marcar, no canvia res.',
	'Click “Check friendly URLs” to refresh rewrite rules. If this warning stays, open Settings → Permalinks, choose Post name (structure /%postname%/), and click Save Changes.':
		'Feu clic a «Comprova les URL amigables» per refrescar les regles. Si l’avís continua, obriu Configuració → Enllaços permanents, trieu Nom de l’entrada (/%postname%/) i deseu.',
	'Correct setup: Settings → Permalinks → Post name. The structure box should contain /%postname%/. Then click Save Changes on that screen.':
		'Configuració correcta: Configuració → Enllaços permanents → Nom de l’entrada. El camp ha de contenir /%postname%/. Després deseu.',
	'Create an API key at https://admin.mailchimp.com/account/api/ and copy the Audience (list) ID from Audience → Settings.':
		'Creeu una clau API a https://admin.mailchimp.com/account/api/ i copieu l’ID d’audiència (llista) des d’Audience → Settings.',
	'Current profile picture': 'Imatge de perfil actual',
	'Default applies to every role that does not have its own address. A role box overrides Default only for people with that role.':
		'Per defecte s’aplica a cada rol sense adreça pròpia. Un camp per rol substitueix el valor per defecte només per a aquell rol.',
	'Each box wants a full address that starts with https:// (a path such as /shop/ is not enough). Empty means keep the WordPress default for that case.':
		'Cada camp vol una adreça completa que comenci amb https:// (un camí com /shop/ no basta). Buit = valor per defecte de WordPress.',
	'Each person turns it on from their own profile page (Profiles) or from Users → Profile in wp-admin. Checking the box and saving creates a key the first time. They type that key into an app such as Google Authenticator.':
		'Cada persona l’activa des del perfil (Perfils) o des d’Usuaris → Perfil a wp-admin. Marcar i desar crea la clau la primera vegada. L’introdueixen a una app com Google Authenticator.',
	'Email confirmation sends a link. The new person cannot log in until they open it.':
		'La confirmació per correu envia un enllaç. La persona nova no pot entrar fins que l’obri.',
	'Example. After login → Default: %1$s (everyone). Subscriber: %2$s (only subscribers; they ignore Default). Administrator: %3$s. After logout → Default: %1$s. After registration → Default: %4$s. Leave Administrator empty under After login if those users should follow Default.':
		'Exemple. Després d’entrar → Per defecte: %1$s (tothom). Subscriptor: %2$s (només subscriptors; ignoren Per defecte). Administrador: %3$s. Després de sortir → Per defecte: %1$s. Després del registre → Per defecte: %4$s. Deixeu Administrador buit a Després d’entrar si han de seguir Per defecte.',
	'Example: a login prompt only guests see': 'Exemple: un avís d’accés que només veuen els visitants',
	'Example: a note only members see': 'Exemple: una nota que només veuen els membres',
	'Example: one role only': 'Exemple: només un rol',
	'Follow the numbered steps on each provider card. Copy the redirect URI shown there into that provider’s console.':
		'Seguiu els passos numerats de cada proveïdor. Copieu l’URI de redirecció a la consola d’aquell proveïdor.',
	Forms: 'Formularis',
	'Friendly URL check finished: WordPress is using Plain permalinks. Set Permalinks to Post name (/%postname%/) — see the note in URL slugs.':
		'Comprovació d’URL amigables acabada: WordPress usa enllaços Plain. Poseu Nom de l’entrada (/%postname%/) — vegeu la nota a URL slugs.',
	'Friendly URL check finished: permalinks are not Plain and the login rewrite rule is present.':
		'Comprovació d’URL amigables acabada: els enllaços no són Plain i la regla d’accés hi és.',
	'Friendly URL check finished: rewrite rules were refreshed but the login rule is still missing. Open Settings → Permalinks and click Save Changes.':
		'Comprovació acabada: s’han refrescat les regles però falta la d’accés. Obriu Configuració → Enllaços permanents i deseu.',
	'Friendly URLs are off. WordPress is using Plain permalinks, so addresses such as /login/ will not open.':
		'Les URL amigables estan desactivades. WordPress usa Plain, així que /login/ no s’obrirà.',
	'Friendly URLs are on (current structure: %s). The login rewrite rule is present, so the preview addresses below can open.':
		'Les URL amigables estan actives (estructura: %s). Hi ha la regla d’accés; les adreces de previsualització poden obrir-se.',
	'Guests see nothing from that block. Logged-in users see the sentence.':
		'Els visitants no veuen res d’aquell bloc. Els usuaris connectats veuen la frase.',
	'Hide wp-login.php returns 404 for the default login file. Use it only together with this plugin’s frontend login address.':
		'Amagar wp-login.php retorna 404 al fitxer d’accés per defecte. Useu-ho només amb l’adreça d’accés frontend d’aquest plugin.',
	'If either box is on, Core → “Log users in after registration” is ignored for that signup. The visitor stays logged out until the pending step is finished. If both boxes are off, the Core option applies as usual.':
		'Si alguna casella està activa, Nucli → «Inicia sessió després del registre» s’ignora. El visitant roman desconnectat fins al pas pendent. Si totes dues estan desactivades, s’aplica l’opció del nucli.',
	'Installed plugins check: none of the plugins on this site match the known incompatibilities for this module.':
		'Comprovació de plugins: cap dels instal·lats coincideix amb incompatibilitats conegudes d’aquest mòdul.',
	'Installed plugins that may clash:': 'Plugins instal·lats que poden xocar:',
	'JPG, PNG, GIF or WebP.': 'JPG, PNG, GIF o WebP.',
	'Keep “Show on posts” checked to put Add/Remove at the end of each single post for logged-in users.':
		'Manteniu «Mostra a les entrades» marcat per posar Afegeix/Treu al final de cada entrada per a usuaris connectats.',
	'Key to type into the app (Google Authenticator or similar):':
		'Clau per introduir a l’app (Google Authenticator o similar):',
	'Leave this unchecked if the password is enough. Check it and click Save Profile to turn two-factor on. The key is created only when you save, not when you tick the box. After saving you will see a key: type it into an authenticator app. From the next login, the password alone is not enough — the site also asks for the 6-digit code from that app.':
		'Deixeu-ho desmarcat si basta la contrasenya. Marqueu-ho i feu clic a Desa el perfil per activar el doble factor. La clau es crea en desar, no en marcar. Després veureu una clau: introduïu-la a una app. Al proper inici, la contrasenya sola no basta: també cal el codi de 6 dígits.',
	'Logged-in users open %s to edit display name, email and password. The path is the Profile slug under Core.':
		'Els usuaris connectats obren %s per editar nom visible, correu i contrasenya. El camí és l’slug de Perfil a Nucli.',
	'Logged-in users see nothing from that block. Guests see the sentence.':
		'Els usuaris connectats no veuen res d’aquell bloc. Els visitants veuen la frase.',
	'Login Protect asks for an email code after the password. Do not also require an authenticator code on the same accounts.':
		'Login Protect demana un codi per correu després de la contrasenya. No exigeixi també un codi d’autenticador als mateixos comptes.',
	'Login Protect is hiding wp-login.php or locking failed logins. Leave hide-login and lockouts off in this plugin while that is on.':
		'Login Protect amaga wp-login.php o bloqueja inicis fallits. Deixeu amagar-login i bloquejos desactivats aquí mentre allò estigui actiu.',
	'Moderation is currently holding new accounts, so this option is not applied.':
		'La moderació està retenint comptes nous, així que aquesta opció no s’aplica.',
	'Only users with the Subscriber role see that sentence. Use the role slug (subscriber, customer, editor), not the translated name. Several roles: role="subscriber,customer". Everyone else, including guests, sees nothing from that block.':
		'Només els usuaris amb el rol Subscriber veuen aquella frase. Useu l’slug (subscriber, customer, editor), no el nom traduït. Diversos rols: role="subscriber,customer". La resta, inclosos visitants, no veuen res del bloc.',
	'Open Permalinks settings': 'Obre la configuració d’enllaços permanents',
	'Paste a shortcode into the content of a post or a page. In the block editor, add a Shortcode block and paste the whole snippet, including the closing tag. In the classic editor, paste it in the text. The words between the opening tag and the closing tag are the part that is shown or hidden. Text outside the shortcode stays visible to everyone. These tags do nothing in a menu or in a widget unless that area runs shortcodes.':
		'Enganxeu un shortcode al contingut d’una entrada o pàgina. A l’editor de blocs, afegiu un bloc Shortcode. A l’editor clàssic, enganxeu-lo al text. El text entre les etiquetes és el que es mostra o s’amaga. Fora del shortcode tothom el veu. No fan res a menús o ginys tret que executin shortcodes.',
	'Paste the API key and the list ID below. With “Ask for newsletter consent” on, only people who tick the box on the registration form are added. Nothing is sent while the key or the list ID is empty.':
		'Enganxeu la clau API i l’ID de llista. Amb «Demanar consentiment del butlletí» actiu, només s’afegeixen qui marquin la casella al registre. No s’envia res si clau o llista són buits.',
	'Paste the client ID (or app ID) and the secret for each provider you want. Leave a provider’s fields empty to hide its button.':
		'Enganxeu l’ID de client (o d’app) i el secret de cada proveïdor. Deixeu camps buits per amagar el botó.',
	'Paste the site key and the secret key below, then save. The checkbox appears on this plugin’s login, register and lost-password forms. Empty keys mean the check stays off.':
		'Enganxeu la clau del lloc i la secreta, i deseu. La casella surt als formularis d’accés, registre i contrasenya perduda. Claus buides = comprovació desactivada.',
	'Permalinks are not Plain (current structure: %s) but the login rewrite rule is missing, so the virtual address may 404.':
		'Els enllaços no són Plain (estructura: %s) però falta la regla d’accés; l’adreça virtual pot donar 404.',
	'Please log in at %s to read the rest of this page.':
		'Inicieu la sessió a %s per llegir la resta d’aquesta pàgina.',
	'Private site is optional and separate from shortcodes: guests are sent to the login page, while login and register stay public.':
		'Lloc privat és opcional i separat dels shortcodes: els visitants van a l’accés; accés i registre romanen públics.',
	'Registration received. You are not signed in yet: confirm your email and/or wait until an administrator approves the account.':
		'Registre rebut. Encara no heu iniciat sessió: confirmeu el correu i/o espereu l’aprovació d’un administrador.',
	'Require an app code at login (two-factor)': 'Exigeix un codi d’app a l’entrada (doble factor)',
	'Send login, registration and password forms without reloading the whole page':
		'Envia formularis d’accés, registre i contrasenya sense recarregar tota la pàgina',
	'Shortcodes go inside a post or page (Shortcode block, or the text area in the classic editor). They hide only the text written between the opening tag and the closing tag. Examples are under Settings below.':
		'Els shortcodes van dins d’una entrada o pàgina (bloc Shortcode o editor clàssic). Només amaguen el text entre les etiquetes. Exemples a Configuració més avall.',
	'Subscriber offer: use the code WELCOME.': 'Oferta per a subscriptors: useu el codi WELCOME.',
	'The saved list is the Favorites slug from Core, currently %s.':
		'La llista desada és l’slug de Preferits del Nucli, actualment %s.',
	'This also overrides Core → “Log users in after registration”. The new person stays logged out until an administrator approves them on the Users screen, even if that Core box is checked. If both moderation boxes are off, the Core option signs them in as usual.':
		'També anul·la Nucli → «Inicia sessió després del registre». La persona nova roman desconnectada fins a l’aprovació a Usuaris, encara que la casella del nucli estigui marcada. Si les dues de moderació són off, el nucli inicia sessió com sempre.',
	'This overrides Core → “Log users in after registration”. The new person stays logged out until they open the link in the email, even if that Core box is checked.':
		'Anul·la Nucli → «Inicia sessió després del registre». La persona nova roman desconnectada fins que obri l’enllaç del correu, encara que la casella del nucli estigui marcada.',
	'Tick HTML emails only when the body contains HTML tags. Otherwise leave it off and write plain text.':
		'Marqueu correus HTML només si el cos té etiquetes HTML. Altrament deixeu-ho off i escriviu text pla.',
	'Tick the roles that must use a second code, then save. People in other roles keep password-only login.':
		'Marqueu els rols que han d’usar un segon codi i deseu. La resta manté només contrasenya.',
	'Turn on Avatars as well if the profile page should accept a photo upload.':
		'Activeu també Avatars si la pàgina de perfil ha d’acceptar una foto.',
	'Turn on lockouts, then set how many failures are allowed and how many minutes the lock lasts. Minimum password length 0 means no extra rule.':
		'Activeu els bloquejos, després definiu fallades permeses i minuts de bloqueig. Longitud mínima 0 = sense regla extra.',
	'Two-factor is already on for this account. The key below is the one your authenticator app should contain. Saving again with the box checked does not create a new key. Uncheck the box and click Save Profile to turn two-factor off: the key is deleted and the app code stops working. Login then asks for the password only.':
		'El doble factor ja està actiu en aquest compte. La clau de sota és la que ha de tenir l’app. Tornar a desar amb la casella marcada no crea una clau nova. Desmarqueu i feu clic a Desa el perfil per desactivar-lo: se suprimeix la clau i el codi deixa de servir. Llavors l’entrada només demana la contrasenya.',
	'Two-factor is already on. Saving with the box checked keeps this same key. Uncheck and update the profile to turn it off and delete the key.':
		'El doble factor ja està actiu. Desar amb la casella marcada manté la mateixa clau. Desmarqueu i actualitzeu el perfil per desactivar-lo i esborrar la clau.',
	'Type a full address, including https:// and your domain. A path alone (/shop/) is rejected by the browser. Leave a box empty to keep the WordPress default for that case: usually the dashboard after login, the home page after logout, and the login page after registration.':
		'Escriviu una adreça completa amb https:// i el domini. Un camí sol (/shop/) el rebutja el navegador. Camp buit = valor per defecte de WordPress: sol ser el tauler després d’entrar, l’inici després de sortir i l’accés després del registre.',
	'Use the check above. Friendly addresses work only when Permalinks is not Plain.':
		'Useu la comprovació de dalt. Les adreces amigables només funcionen si els enllaços no són Plain.',
	'Use the placeholders listed under each field (for example {user_login}, {reset_url}). An empty field keeps the WordPress default text.':
		'Useu els marcadors sota cada camp (p. ex. {user_login}, {reset_url}). Camp buit = text per defecte de WordPress.',
	'Welcome back. Your downloads are on this page.': 'Benvingut de nou. Les baixades són en aquesta pàgina.',
	'What to type in the boxes': 'Què escriure als camps',
	'While logged in, open %s. The page says whether a photo is already saved. Choose a JPG, PNG, GIF or WebP to replace it. The file is stored in uploads/tso-login-organizer/ and replaces Gravatar for that user.':
		'Amb la sessió iniciada, obriu %s. La pàgina indica si ja hi ha foto. Trieu JPG, PNG, GIF o WebP per substituir-la. El fitxer va a uploads/tso-login-organizer/ i substitueix Gravatar.',
	'With this on, the visitor stays on the same screen and only the form updates (this is called AJAX). With this off, the browser loads the page again after each submit. Turn it off if another plugin already updates these forms in place.':
		'Activat: el visitant roman a la mateixa pantalla i només s’actualitza el formulari (AJAX). Desactivat: el navegador torna a carregar la pàgina. Desactiveu-ho si un altre plugin ja actualitza aquests formularis.',
	'You already have a profile picture. It is shown below. Choose a new file only if you want to replace it. Leave the file field empty to keep this one.':
		'Ja teniu una imatge de perfil. Es mostra a sota. Trieu un fitxer nou només si voleu substituir-la. Deixeu el camp buit per mantenir-la.',
	'You do not have a profile picture yet. Until you upload one, the site uses Gravatar or the default avatar.':
		'Encara no teniu imatge de perfil. Fins que en pugeu una, el lloc usa Gravatar o l’avatar per defecte.',
	'active — likely clash': 'actiu — possible xoc',
	'installed, not active': 'instal·lat, no actiu',
};

const es = {
	Account: 'Cuenta',
	'Admin approval leaves the account pending on the Users screen until an administrator approves it.':
		'La aprobación del administrador deja la cuenta pendiente en Usuarios hasta que un administrador la apruebe.',
	'After a successful registration the new person is signed in immediately. If Moderation has email confirmation or admin approval turned on, this box is ignored and they stay logged out until that step is finished.':
		'Tras un registro correcto la persona nueva inicia sesión al momento. Si Moderación tiene confirmación por correo o aprobación activa, esta casilla se ignora y permanece desconectada hasta terminar ese paso.',
	'After that, login asks for the password and for the 6-digit code shown in the app.':
		'Después, el inicio de sesión pide la contraseña y el código de 6 dígitos de la aplicación.',
	'Also adds a captcha or anti-spam check on forms. Two checks on the same form often fail verification.':
		'También añade captcha o antispam a los formularios. Dos comprobaciones en el mismo formulario suelen fallar.',
	'Also adds social login buttons or OAuth callbacks. Buttons can be duplicated or the return URL can be stolen by the other plugin.':
		'También añade botones de acceso social o retornos OAuth. Los botones pueden duplicarse o el otro plugin puede capturar la URL de retorno.',
	'Also asks for a second code at login. Two authenticators on the same account block sign-in.':
		'También pide un segundo código al entrar. Dos autenticadores en la misma cuenta bloquean el acceso.',
	'Also blocks login until a registration is approved (wp_authenticate_user). Keep only one approval plugin active.':
		'También bloquea el acceso hasta aprobar el registro (wp_authenticate_user). Deja solo un plugin de aprobación activo.',
	'Also blocks wp-admin or replaces the profile screen. Subscribers may be unable to reach either profile.':
		'También bloquea wp-admin o sustituye la pantalla de perfil. Los suscriptores pueden no llegar a ningún perfil.',
	'Also forces login or restricts content. Guests may be redirected twice or locked out of pages.':
		'También fuerza el acceso o restringe contenido. Los visitantes pueden redirigirse dos veces o quedar fuera de páginas.',
	'Also locks out failed logins or hides wp-login.php. Running both can block legitimate users or hide the login URL twice.':
		'También bloquea inicios fallidos u oculta wp-login.php. Ambos juntos pueden bloquear usuarios válidos u ocultar la URL de acceso dos veces.',
	'Also replaces outgoing email content. The message saved here may be wrapped or discarded.':
		'También sustituye el contenido de los correos salientes. El mensaje guardado aquí puede envolverse o descartarse.',
	'Also replaces the profile photo (get_avatar). One of the two pictures will win and the other upload may look ignored.':
		'También sustituye la foto de perfil (get_avatar). Una de las dos imágenes gana y la otra subida puede parecer ignorada.',
	'Also sends new users to Mailchimp. The same person can be subscribed twice.':
		'También envía usuarios nuevos a Mailchimp. La misma persona puede suscribirse dos veces.',
	'Also sends users somewhere after login, logout or registration. The last plugin to run decides the final URL.':
		'También envía usuarios tras entrar, salir o registrarse. El último plugin decide la URL final.',
	'Also stores favorite posts. The buttons and the list page may disagree about what is saved.':
		'También guarda entradas favoritas. Los botones y la página de lista pueden no coincidir.',
	'Avatars needs Profiles. The upload field is on the profile page, not on this screen. Max size (KB) below is the only setting here.':
		'Avatares necesita Perfiles. El campo de subida está en la página de perfil, no aquí. El tamaño máximo (KB) es el único ajuste de esta pantalla.',
	'Block wp-admin sends users who cannot edit posts away from /wp-admin/ to that profile page and hides the admin bar.':
		'Bloquear wp-admin envía a los usuarios sin edit_posts fuera de /wp-admin/ a esa página de perfil y oculta la barra de administración.',
	'Check friendly URLs': 'Comprobar URL amigables',
	'Check the box and update the profile to turn two-factor on. A key is created only then. The user must add that key to an authenticator app. The next login will ask for the password and for the 6-digit code from the app. Leaving the box unchecked changes nothing.':
		'Marca la casilla y actualiza el perfil para activar el doble factor. La clave se crea solo entonces. El usuario debe añadirla a una app de autenticación. El próximo inicio pedirá la contraseña y el código de 6 dígitos. Si dejas la casilla sin marcar, no cambia nada.',
	'Click “Check friendly URLs” to refresh rewrite rules. If this warning stays, open Settings → Permalinks, choose Post name (structure /%postname%/), and click Save Changes.':
		'Haz clic en «Comprobar URL amigables» para refrescar las reglas. Si el aviso sigue, abre Ajustes → Enlaces permanentes, elige Nombre de la entrada (/%postname%/) y guarda.',
	'Correct setup: Settings → Permalinks → Post name. The structure box should contain /%postname%/. Then click Save Changes on that screen.':
		'Configuración correcta: Ajustes → Enlaces permanentes → Nombre de la entrada. El campo debe contener /%postname%/. Luego guarda.',
	'Create an API key at https://admin.mailchimp.com/account/api/ and copy the Audience (list) ID from Audience → Settings.':
		'Crea una clave API en https://admin.mailchimp.com/account/api/ y copia el ID de audiencia (lista) desde Audience → Settings.',
	'Current profile picture': 'Imagen de perfil actual',
	'Default applies to every role that does not have its own address. A role box overrides Default only for people with that role.':
		'Por defecto se aplica a cada rol sin dirección propia. Un campo por rol sustituye el valor por defecto solo para ese rol.',
	'Each box wants a full address that starts with https:// (a path such as /shop/ is not enough). Empty means keep the WordPress default for that case.':
		'Cada campo quiere una dirección completa que empiece por https:// (una ruta como /shop/ no basta). Vacío = valor por defecto de WordPress.',
	'Each person turns it on from their own profile page (Profiles) or from Users → Profile in wp-admin. Checking the box and saving creates a key the first time. They type that key into an app such as Google Authenticator.':
		'Cada persona lo activa desde su perfil (Perfiles) o desde Usuarios → Perfil en wp-admin. Marcar y guardar crea la clave la primera vez. La introducen en una app como Google Authenticator.',
	'Email confirmation sends a link. The new person cannot log in until they open it.':
		'La confirmación por correo envía un enlace. La persona nueva no puede entrar hasta abrirlo.',
	'Example. After login → Default: %1$s (everyone). Subscriber: %2$s (only subscribers; they ignore Default). Administrator: %3$s. After logout → Default: %1$s. After registration → Default: %4$s. Leave Administrator empty under After login if those users should follow Default.':
		'Ejemplo. Tras entrar → Por defecto: %1$s (todos). Suscriptor: %2$s (solo suscriptores; ignoran Por defecto). Administrador: %3$s. Tras salir → Por defecto: %1$s. Tras el registro → Por defecto: %4$s. Deja Administrador vacío en Tras entrar si deben seguir Por defecto.',
	'Example: a login prompt only guests see': 'Ejemplo: un aviso de acceso que solo ven los visitantes',
	'Example: a note only members see': 'Ejemplo: una nota que solo ven los miembros',
	'Example: one role only': 'Ejemplo: solo un rol',
	'Follow the numbered steps on each provider card. Copy the redirect URI shown there into that provider’s console.':
		'Sigue los pasos numerados de cada proveedor. Copia el URI de redirección en la consola de ese proveedor.',
	Forms: 'Formularios',
	'Friendly URL check finished: WordPress is using Plain permalinks. Set Permalinks to Post name (/%postname%/) — see the note in URL slugs.':
		'Comprobación de URL amigables terminada: WordPress usa enlaces Plain. Pon Nombre de la entrada (/%postname%/) — ver la nota en URL slugs.',
	'Friendly URL check finished: permalinks are not Plain and the login rewrite rule is present.':
		'Comprobación de URL amigables terminada: los enlaces no son Plain y la regla de acceso está presente.',
	'Friendly URL check finished: rewrite rules were refreshed but the login rule is still missing. Open Settings → Permalinks and click Save Changes.':
		'Comprobación terminada: se refrescaron las reglas pero falta la de acceso. Abre Ajustes → Enlaces permanentes y guarda.',
	'Friendly URLs are off. WordPress is using Plain permalinks, so addresses such as /login/ will not open.':
		'Las URL amigables están desactivadas. WordPress usa Plain, así que /login/ no se abrirá.',
	'Friendly URLs are on (current structure: %s). The login rewrite rule is present, so the preview addresses below can open.':
		'Las URL amigables están activas (estructura: %s). Hay regla de acceso; las direcciones de vista previa pueden abrirse.',
	'Guests see nothing from that block. Logged-in users see the sentence.':
		'Los visitantes no ven nada de ese bloque. Los usuarios conectados ven la frase.',
	'Hide wp-login.php returns 404 for the default login file. Use it only together with this plugin’s frontend login address.':
		'Ocultar wp-login.php devuelve 404 al archivo de acceso por defecto. Úsalo solo con la dirección de acceso frontend de este plugin.',
	'If either box is on, Core → “Log users in after registration” is ignored for that signup. The visitor stays logged out until the pending step is finished. If both boxes are off, the Core option applies as usual.':
		'Si alguna casilla está activa, Núcleo → «Iniciar sesión tras el registro» se ignora. El visitante permanece desconectado hasta el paso pendiente. Si ambas están desactivadas, se aplica la opción del núcleo.',
	'Installed plugins check: none of the plugins on this site match the known incompatibilities for this module.':
		'Comprobación de plugins: ninguno de los instalados coincide con incompatibilidades conocidas de este módulo.',
	'Installed plugins that may clash:': 'Plugins instalados que pueden chocar:',
	'JPG, PNG, GIF or WebP.': 'JPG, PNG, GIF o WebP.',
	'Keep “Show on posts” checked to put Add/Remove at the end of each single post for logged-in users.':
		'Mantén «Mostrar en entradas» marcado para poner Añadir/Quitar al final de cada entrada para usuarios conectados.',
	'Key to type into the app (Google Authenticator or similar):':
		'Clave para introducir en la app (Google Authenticator o similar):',
	'Leave this unchecked if the password is enough. Check it and click Save Profile to turn two-factor on. The key is created only when you save, not when you tick the box. After saving you will see a key: type it into an authenticator app. From the next login, the password alone is not enough — the site also asks for the 6-digit code from that app.':
		'Déjalo desmarcado si basta la contraseña. Márcalo y haz clic en Guardar perfil para activar el doble factor. La clave se crea al guardar, no al marcar. Después verás una clave: introdúcela en una app. En el próximo inicio, la contraseña sola no basta: también pide el código de 6 dígitos.',
	'Logged-in users open %s to edit display name, email and password. The path is the Profile slug under Core.':
		'Los usuarios conectados abren %s para editar nombre visible, correo y contraseña. La ruta es el slug de Perfil en Núcleo.',
	'Logged-in users see nothing from that block. Guests see the sentence.':
		'Los usuarios conectados no ven nada de ese bloque. Los visitantes ven la frase.',
	'Login Protect asks for an email code after the password. Do not also require an authenticator code on the same accounts.':
		'Login Protect pide un código por correo tras la contraseña. No exijas también un código de autenticador en las mismas cuentas.',
	'Login Protect is hiding wp-login.php or locking failed logins. Leave hide-login and lockouts off in this plugin while that is on.':
		'Login Protect oculta wp-login.php o bloquea inicios fallidos. Deja ocultar-login y bloqueos desactivados aquí mientras aquello esté activo.',
	'Moderation is currently holding new accounts, so this option is not applied.':
		'La moderación está reteniendo cuentas nuevas, así que esta opción no se aplica.',
	'Only users with the Subscriber role see that sentence. Use the role slug (subscriber, customer, editor), not the translated name. Several roles: role="subscriber,customer". Everyone else, including guests, sees nothing from that block.':
		'Solo los usuarios con el rol Subscriber ven esa frase. Usa el slug (subscriber, customer, editor), no el nombre traducido. Varios roles: role="subscriber,customer". El resto, incluidos visitantes, no ven nada del bloque.',
	'Open Permalinks settings': 'Abrir ajustes de enlaces permanentes',
	'Paste a shortcode into the content of a post or a page. In the block editor, add a Shortcode block and paste the whole snippet, including the closing tag. In the classic editor, paste it in the text. The words between the opening tag and the closing tag are the part that is shown or hidden. Text outside the shortcode stays visible to everyone. These tags do nothing in a menu or in a widget unless that area runs shortcodes.':
		'Pega un shortcode en el contenido de una entrada o página. En el editor de bloques, añade un bloque Shortcode. En el clásico, pégalo en el texto. El texto entre las etiquetas es lo que se muestra u oculta. Fuera del shortcode todos lo ven. No hacen nada en menús o widgets salvo que ejecuten shortcodes.',
	'Paste the API key and the list ID below. With “Ask for newsletter consent” on, only people who tick the box on the registration form are added. Nothing is sent while the key or the list ID is empty.':
		'Pega la clave API y el ID de lista. Con «Pedir consentimiento del boletín» activo, solo se añaden quienes marquen la casilla en el registro. No se envía nada si clave o lista están vacíos.',
	'Paste the client ID (or app ID) and the secret for each provider you want. Leave a provider’s fields empty to hide its button.':
		'Pega el ID de cliente (o de app) y el secreto de cada proveedor. Deja campos vacíos para ocultar el botón.',
	'Paste the site key and the secret key below, then save. The checkbox appears on this plugin’s login, register and lost-password forms. Empty keys mean the check stays off.':
		'Pega la clave del sitio y la secreta, y guarda. La casilla aparece en los formularios de acceso, registro y contraseña perdida. Claves vacías = comprobación desactivada.',
	'Permalinks are not Plain (current structure: %s) but the login rewrite rule is missing, so the virtual address may 404.':
		'Los enlaces no son Plain (estructura: %s) pero falta la regla de acceso; la dirección virtual puede dar 404.',
	'Please log in at %s to read the rest of this page.':
		'Inicia sesión en %s para leer el resto de esta página.',
	'Private site is optional and separate from shortcodes: guests are sent to the login page, while login and register stay public.':
		'Sitio privado es opcional y aparte de los shortcodes: los visitantes van al acceso; acceso y registro siguen públicos.',
	'Registration received. You are not signed in yet: confirm your email and/or wait until an administrator approves the account.':
		'Registro recibido. Aún no has iniciado sesión: confirma el correo y/o espera la aprobación de un administrador.',
	'Require an app code at login (two-factor)': 'Exigir un código de app al entrar (doble factor)',
	'Send login, registration and password forms without reloading the whole page':
		'Enviar formularios de acceso, registro y contraseña sin recargar toda la página',
	'Shortcodes go inside a post or page (Shortcode block, or the text area in the classic editor). They hide only the text written between the opening tag and the closing tag. Examples are under Settings below.':
		'Los shortcodes van dentro de una entrada o página (bloque Shortcode o editor clásico). Solo ocultan el texto entre las etiquetas. Ejemplos en Ajustes más abajo.',
	'Subscriber offer: use the code WELCOME.': 'Oferta para suscriptores: usa el código WELCOME.',
	'The saved list is the Favorites slug from Core, currently %s.':
		'La lista guardada es el slug de Favoritos del Núcleo, actualmente %s.',
	'This also overrides Core → “Log users in after registration”. The new person stays logged out until an administrator approves them on the Users screen, even if that Core box is checked. If both moderation boxes are off, the Core option signs them in as usual.':
		'También anula Núcleo → «Iniciar sesión tras el registro». La persona nueva permanece desconectada hasta la aprobación en Usuarios, aunque la casilla del núcleo esté marcada. Si ambas de moderación están off, el núcleo inicia sesión como siempre.',
	'This overrides Core → “Log users in after registration”. The new person stays logged out until they open the link in the email, even if that Core box is checked.':
		'Anula Núcleo → «Iniciar sesión tras el registro». La persona nueva permanece desconectada hasta abrir el enlace del correo, aunque la casilla del núcleo esté marcada.',
	'Tick HTML emails only when the body contains HTML tags. Otherwise leave it off and write plain text.':
		'Marca correos HTML solo si el cuerpo tiene etiquetas HTML. Si no, déjalo off y escribe texto plano.',
	'Tick the roles that must use a second code, then save. People in other roles keep password-only login.':
		'Marca los roles que deben usar un segundo código y guarda. El resto mantiene solo contraseña.',
	'Turn on Avatars as well if the profile page should accept a photo upload.':
		'Activa también Avatares si la página de perfil debe aceptar una foto.',
	'Turn on lockouts, then set how many failures are allowed and how many minutes the lock lasts. Minimum password length 0 means no extra rule.':
		'Activa los bloqueos, luego define fallos permitidos y minutos de bloqueo. Longitud mínima 0 = sin regla extra.',
	'Two-factor is already on for this account. The key below is the one your authenticator app should contain. Saving again with the box checked does not create a new key. Uncheck the box and click Save Profile to turn two-factor off: the key is deleted and the app code stops working. Login then asks for the password only.':
		'El doble factor ya está activo en esta cuenta. La clave de abajo es la que debe tener la app. Guardar otra vez con la casilla marcada no crea una clave nueva. Desmarca y haz clic en Guardar perfil para desactivarlo: se borra la clave y el código deja de servir. Entonces el acceso solo pide la contraseña.',
	'Two-factor is already on. Saving with the box checked keeps this same key. Uncheck and update the profile to turn it off and delete the key.':
		'El doble factor ya está activo. Guardar con la casilla marcada mantiene la misma clave. Desmarca y actualiza el perfil para desactivarlo y borrar la clave.',
	'Type a full address, including https:// and your domain. A path alone (/shop/) is rejected by the browser. Leave a box empty to keep the WordPress default for that case: usually the dashboard after login, the home page after logout, and the login page after registration.':
		'Escribe una dirección completa con https:// y tu dominio. Una ruta sola (/shop/) la rechaza el navegador. Campo vacío = valor por defecto de WordPress: suele ser el escritorio tras entrar, la portada tras salir y el acceso tras el registro.',
	'Use the check above. Friendly addresses work only when Permalinks is not Plain.':
		'Usa la comprobación de arriba. Las direcciones amigables solo funcionan si los enlaces no son Plain.',
	'Use the placeholders listed under each field (for example {user_login}, {reset_url}). An empty field keeps the WordPress default text.':
		'Usa los marcadores bajo cada campo (p. ej. {user_login}, {reset_url}). Campo vacío = texto por defecto de WordPress.',
	'Welcome back. Your downloads are on this page.': 'Bienvenido de nuevo. Las descargas están en esta página.',
	'What to type in the boxes': 'Qué escribir en los campos',
	'While logged in, open %s. The page says whether a photo is already saved. Choose a JPG, PNG, GIF or WebP to replace it. The file is stored in uploads/tso-login-organizer/ and replaces Gravatar for that user.':
		'Con la sesión iniciada, abre %s. La página indica si ya hay foto. Elige JPG, PNG, GIF o WebP para sustituirla. El archivo va a uploads/tso-login-organizer/ y sustituye Gravatar.',
	'With this on, the visitor stays on the same screen and only the form updates (this is called AJAX). With this off, the browser loads the page again after each submit. Turn it off if another plugin already updates these forms in place.':
		'Activado: el visitante permanece en la misma pantalla y solo se actualiza el formulario (AJAX). Desactivado: el navegador vuelve a cargar la página. Desactívalo si otro plugin ya actualiza estos formularios.',
	'You already have a profile picture. It is shown below. Choose a new file only if you want to replace it. Leave the file field empty to keep this one.':
		'Ya tienes una imagen de perfil. Se muestra abajo. Elige un archivo nuevo solo si quieres sustituirla. Deja el campo vacío para mantenerla.',
	'You do not have a profile picture yet. Until you upload one, the site uses Gravatar or the default avatar.':
		'Aún no tienes imagen de perfil. Hasta que subas una, el sitio usa Gravatar o el avatar por defecto.',
	'active — likely clash': 'activo — posible choque',
	'installed, not active': 'instalado, no activo',
};

module.exports = { ca, es };
