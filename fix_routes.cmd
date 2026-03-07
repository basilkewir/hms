@echo off
powershell -Command "$backup = Get-Content 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_backup.php'; $backup | Select-Object -First 1868 | Set-Content 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php'"
powershell -Command "Move-Item -Force 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php' 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web.php'"
del "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php"
