@echo off

cd /d %~dp0

del .\log\*.log

\php\php.exe -f Main.php
