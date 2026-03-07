$content = Get-Content "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_backup.php" | Select-Object -First 1868
Set-Content "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php" -Value $content
Move-Item -Force "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php" "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web.php"
