@echo off
copy "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_backup.php" "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php"
powershell -Command "(Get-Content 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php' | Select-Object -First 1868 | Set-Content 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web.php')"
move /Y "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php" "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web.php"
del "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php"
