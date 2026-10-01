<?php

session_start();

unset($_SESSION["siswa_id"]);
unset($_SESSION["siswa_name"]);
unset($_SESSION["siswa_email"]);
unset($_SESSION["siswa_nisn"]);

session_destroy();

header(
    "Location: login_siswa.html"
);

exit;