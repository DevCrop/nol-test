<?php
// Retired unguarded endpoint; public and admin use their scoped download routes.
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
exit('Not Found');
