# Hotspot Captive Portal Fix Commands

# 1. Check if hotspot is running
/ip hotspot print

# 2. If not running, create hotspot properly
/ip hotspot
add name=hotspot1 interface=bridge1 address-pool=dhcp-pool1 profile=hsprof1 disabled=no

# 3. Configure hotspot profile with proper settings
/ip hotspot profile
set [find name=hsprof1] \
hotspot-address=192.168.10.1 \
dns-name="hoteldonzebe.local" \
html-directory=hotspot \
login-by=cookie,http-chap \
rate-limit="" \
http-proxy=0.0.0.0:0 \
smtp-server=0.0.0.0:0 \
popup=yes \
dns-name="hoteldonzebe.local"

# 4. Enable hotspot on the interface
/ip hotspot
enable hotspot1

# 5. Configure hotspot user profile
/ip hotspot user profile
set [find name=default] \
idle-timeout=30m \
keepalive-timeout=2m \
status-autorefresh=1m \
shared-users=1 \
transparent-proxy=yes \
add-mac-cookie=yes

# 6. Add firewall rules for captive portal redirection
/ip firewall filter
add chain=dstnat action=accept dst-port=80,443 protocol=tcp src-address=192.168.10.0/24 comment="Allow hotspot portal"
add chain=dstnat action=redirect dst-port=80 protocol=tcp src-address=192.168.10.0/24 to-ports=80 comment="Redirect to hotspot"

# 7. Check hotspot users
/ip hotspot user print

# 8. Test connectivity
/ping 8.8.8.8 count=3

# 9. Restart hotspot service
/ip hotspot
disable hotspot1
enable hotspot1

# 10. Verify hotspot status
/ip hotspot print
/ip hotspot active print
