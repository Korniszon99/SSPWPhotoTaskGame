<?php
// Proste przekierowanie z root na aplikację w podkatalogu `photo_game/`.
// Ułatwia to działanie pod głównym DNS App Service bez dopisywania ścieżki.
http_response_code(302);
header('Location: /photo_game/');
exit;
