# MikroTik RB951 Deployment Instructions

## Method 1: Copy-Paste Commands (Recommended)

1. Connect to your MikroTik via WinBox or SSH
2. Open Terminal
3. Copy and paste the following commands section by section:

### Basic Setup
```
/system identity set name="RB951-Hotel"
/system clock set time-zone-name=Europe/London
/system ntp client set enabled=yes server-pool=pool.ntp.org

/interface ethernet
set [find name=ether1] name=ether1-wan comment="WAN Port"
set [find name=ether2] name=ether2-lan comment="Hotspot LAN Port 2"
set [find name=ether3] name=ether3-lan comment="Hotspot LAN Port 3"
set [find name=ether4] name=ether4-lan comment="Hotspot LAN Port 4"
set [find name=ether5] name=ether5-lan comment="Bridge 2 LAN Port 5"

/interface wireless
set [find name=wlan1] name=wlan1-hotspot comment="Hotspot WLAN" mode=ap-bridge ssid="Hotel-Donzebe" band=2ghz-b/g/n frequency=auto disabled=no
```

### Bridge Configuration
```
/interface bridge add name=bridge1 comment="Hotspot Bridge"
/interface bridge port add bridge=bridge1 interface=ether2-lan
/interface bridge port add bridge=bridge1 interface=ether3-lan
/interface bridge port add bridge=bridge1 interface=ether4-lan
/interface bridge port add bridge=bridge1 interface=wlan1-hotspot

/interface bridge add name=bridge2 comment="Regular LAN Bridge"
/interface bridge port add bridge=bridge2 interface=ether5-lan
```

### IP Configuration
```
/ip dhcp-client
add interface=ether1-wan add-default-route=yes comment="WAN DHCP Client"

/ip address add address=192.168.10.1/24 interface=bridge1 comment="Hotspot Network"
/ip address add address=192.168.20.1/24 interface=bridge2 comment="Regular LAN Network"
```

### DHCP Configuration
```
/ip pool add name=dhcp-pool1 ranges=192.168.10.100-192.168.10.200
/ip dhcp-server add name=dhcp1 interface=bridge1 address-pool=dhcp-pool1 disabled=no
/ip dhcp-server network add address=192.168.10.0/24 gateway=192.168.10.1 dns-server=8.8.8.8,8.8.4.4

/ip pool add name=dhcp-pool2 ranges=192.168.20.100-192.168.20.200
/ip dhcp-server add name=dhcp2 interface=bridge2 address-pool=dhcp-pool2 disabled=no
/ip dhcp-server network add address=192.168.20.0/24 gateway=192.168.20.1 dns-server=8.8.8.8,8.8.4.4
```

### NAT Configuration
```
/ip firewall nat
add chain=srcnat out-interface=ether1-wan src-address=192.168.10.0/24 action=masquerade comment="NAT for Hotspot"
add chain=srcnat out-interface=ether1-wan src-address=192.168.20.0/24 action=masquerade comment="NAT for Regular LAN"
```

### Hotspot Configuration
```
/ip hotspot
add name=hotspot1 interface=bridge1 address-pool=dhcp-pool1 profile=hsprof1

/ip hotspot profile
add name=hsprof1 hotspot-address=192.168.10.1 dns-name="hoteldonzebe.local" html-directory=hotspot

/ip hotspot user profile
add name=default idle-timeout=30m keepalive-timeout=2m status-autorefresh=1m shared-users=1

/ip hotspot user add name="hoteldonzebe" password="Donzebe@2024" profile=default comment="Hotel Donzebe Admin"
/ip hotspot user add name="guest" password="guest123" profile=default comment="Guest User"
/ip hotspot user add name="room101" password="room101" profile=default comment="Room 101"
/ip hotspot user add name="room102" password="room102" profile=default comment="Room 102"
/ip hotspot user add name="room103" password="room103" profile=default comment="Room 103"
```

### Firewall Configuration
```
/ip firewall filter
add chain=forward src-address=192.168.10.0/24 dst-address=192.168.20.0/24 action=accept comment="Allow inter-network communication"
add chain=forward src-address=192.168.20.0/24 dst-address=192.168.10.0/24 action=accept comment="Allow inter-network communication"
add chain=forward connection-state=established,related action=accept comment="Allow established connections"
add chain=forward connection-state=invalid action=drop comment="Drop invalid connections"

/ip firewall filter
add chain=forward src-address=192.168.10.0/24 connection-state=new action=jump jump-target=hotspot-auth comment="Jump to hotspot auth check"
add chain=hotspot-auth src-address=192.168.10.0/24 dst-address=!192.168.10.1 protocol=tcp dst-port=80 action=reject reject-with=tcp-reset comment="Block HTTP without auth"
add chain=hotspot-auth src-address=192.168.10.0/24 dst-address=!192.168.10.1 protocol=tcp dst-port=443 action=reject reject-with=tcp-reset comment="Block HTTPS without auth"
add chain=hotspot-auth src-address=192.168.10.0/24 dst-address=!192.168.10.1 protocol=udp dst-port=53 action=accept comment="Allow DNS for hotspot login"
add chain=hotspot-auth src-address=192.168.10.0/24 dst-address=192.168.10.1 action=accept comment="Allow hotspot portal access"
```

### Final Settings
```
/ip settings set allow-fastpath=yes
/ip firewall connection tracking set enabled=yes
```

## Method 2: Upload File via WinBox

1. Open WinBox and connect to your MikroTik
2. Go to Files menu
3. Click "Upload" and select the `mikrotik_rb951_config.rsc` file
4. In Terminal, run: `/import mikrotik_rb951_config.rsc`

## Method 3: FTP Upload

1. Enable FTP on MikroTik: `/ip service enable ftp`
2. Use FTP client to upload `mikrotik_rb951_config.rsc` to the router
3. In Terminal, run: `/import mikrotik_rb951_config.rsc`

## Verification

After deployment, verify:
- WAN: `ip dhcp-client print`
- Bridges: `interface bridge print`
- Hotspot: `ip hotspot print`
- Users: `ip hotspot user print`

## Network Summary
- **WAN**: Port 1 (DHCP)
- **Hotspot**: 192.168.10.1/24 (Ports 2,3,4 + WLAN) - Requires login
- **Regular LAN**: 192.168.20.1/24 (Port 5) - Open access
- **WiFi SSID**: "Hotel-Donzebe" (2.4GHz)
