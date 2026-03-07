# Fresh MikroTik RB951 Configuration
# WAN: Port 1
# Hotspot: Ports 2,3,4 + WLAN (192.168.10.1/24)
# Regular LAN: Port 5 (192.168.30.1/24)

# Reset to factory defaults
/system reset-configuration no-defaults=yes skip-backup=yes

# Wait for reset to complete
:delay 10s

# Basic system settings
/system identity set name="Hotel-Donzebe"
/system clock set time-zone-name=Europe/London
/system ntp client set enabled=yes server-pool=pool.ntp.org

# Configure interfaces
/interface ethernet
set [find name=ether1] name=ether1-wan comment="WAN Port"
set [find name=ether2] name=ether2-lan comment="Hotspot LAN Port 2"
set [find name=ether3] name=ether3-lan comment="Hotspot LAN Port 3"
set [find name=ether4] name=ether4-lan comment="Hotspot LAN Port 4"
set [find name=ether5] name=ether5-lan comment="Regular LAN Port 5"

# Configure wireless
/interface wireless
set [find name=wlan1] name=wlan1-hotspot comment="Hotspot WLAN" mode=ap-bridge ssid="Hotel-Donzebe" band=2ghz-onlyg frequency=2437 channel-width=20mhz tx-power=20 disabled=no

# Create bridges
/interface bridge
add name=bridge1 comment="Hotspot Bridge"
add name=bridge2 comment="Regular LAN Bridge"

# Add ports to bridges
/interface bridge port
add bridge=bridge1 interface=ether2-lan
add bridge=bridge1 interface=ether3-lan
add bridge=bridge1 interface=ether4-lan
add bridge=bridge1 interface=wlan1-hotspot
add bridge=bridge2 interface=ether5-lan

# Configure WAN
/ip dhcp-client
add interface=ether1-wan add-default-route=yes use-peer-dns=yes comment="WAN DHCP Client"

# Configure IP addresses
/ip address
add address=192.168.10.1/24 interface=bridge1 comment="Hotspot Network"
add address=192.168.30.1/24 interface=bridge2 comment="Regular LAN Network"

# Setup DHCP pools
/ip pool
add name=dhcp-pool1 ranges=192.168.10.100-192.168.10.200
add name=dhcp-pool2 ranges=192.168.30.100-192.168.30.200

# Setup DHCP servers
/ip dhcp-server
add name=dhcp1 interface=bridge1 address-pool=dhcp-pool1 disabled=no
add name=dhcp2 interface=bridge2 address-pool=dhcp-pool2 disabled=no

# Setup DHCP networks
/ip dhcp-server network
add address=192.168.10.0/24 gateway=192.168.10.1 dns-server=8.8.8.8,8.8.4.4
add address=192.168.30.0/24 gateway=192.168.30.1 dns-server=8.8.8.8,8.8.4.4

# Configure NAT
/ip firewall nat
add chain=srcnat out-interface=ether1-wan action=masquerade comment="NAT for all networks"

# Configure hotspot
/ip hotspot
add name=hotspot1 interface=bridge1 address-pool=dhcp-pool1 profile=hsprof1 disabled=no

# Configure hotspot profile
/ip hotspot profile
add name=hsprof1 hotspot-address=192.168.10.1 dns-name="hoteldonzebe.local" html-directory=hotspot login-by=cookie,http-chap

# Configure hotspot users
/ip hotspot user
add name="hoteldonzebe" password="Donzebe@2024" profile=default comment="Hotel Donzebe Admin"
add name="guest" password="guest123" profile=default comment="Guest User"
add name="room101" password="room101" profile=default comment="Room 101"
add name="room102" password="room102" profile=default comment="Room 102"
add name="room103" password="room103" profile=default comment="Room 103"

# Configure DNS
/ip dns
set servers=8.8.8.8,8.8.4.4 allow-remote-requests=yes

# Configure firewall
/ip firewall filter
add chain=forward connection-state=established,related action=accept
add chain=forward connection-state=invalid action=drop

# Enable services
/ip service
set www port=80
set www address=0.0.0.0/0

# Save configuration
/system backup save name=hotel-donzebe-setup

:log info "Hotel Donzebe RB951 Configuration Complete"
:log info "WAN: ether1-wan (DHCP)"
:log info "Hotspot: 192.168.10.1/24 (Ports 2,3,4 + WLAN)"
:log info "Regular LAN: 192.168.30.1/24 (Port 5)"
:log info "WiFi SSID: Hotel-Donzebe"
