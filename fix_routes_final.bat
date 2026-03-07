@echo off
powershell -Command "Get-Content 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_backup.php' | Select-Object -First 1868 | Out-File -FilePath 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php' -Encoding utf8"
powershell -Command "Move-Item -Force 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php' 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web.php'"
powershell -Command "Remove-Item 'c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php'"
