<?php
include '_base.php';
session_destroy();
header("Location: $base/login.php");
exit;
