$backupPath = "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_backup.php"
$targetPath = "c:\Users\FT_Basil\Documents\FT_Basil\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web.php"
$tempPath = "c:\Users\FT_Basil\Documents\IPTVPlayerNative\MyApplication\hotel-management-system\routes\web_temp.php"

# Read the backup file and get first 1868 lines
$lines = Get-Content $backupPath | Select-Object -First 1868

# Write to temp file
$lines | Set-Content $tempPath -Encoding UTF8

# Replace the original file
Move-Item -Force $tempPath $targetPath

# Clean up
Remove-Item $tempPath
