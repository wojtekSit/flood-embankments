<?php
session_start();
session_unset();     // usuwa wszystkie zmienne sesyjne
session_destroy();   // niszczy sesję

header("Location: login.php");
exit;
