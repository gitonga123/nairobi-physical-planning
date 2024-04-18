@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../friendsofsymfony1/symfony1/data/bin/symfony
php "%BIN_TARGET%" %*
