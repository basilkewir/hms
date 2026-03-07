# MikroTik RB951 Configuration Script
# WAN: Port 1
# Bridge 1 (Hotspot): Ports 2, 3, 4
# Bridge 2 (No Hotspot): Port 5

# Reset configuration (optional - uncomment if needed)
# /system reset-configuration no-defaults=yes skip-backup=yes

# Basic system settings
/system identity set name="RB951-Hotel"
/system clock set time-zone-name=auto
/system ntp client set enabled=yes servers=pool.ntp.org

# Configure interfaces
/interface ethernet
set [find name=ether1] name=ether1-wan comment="WAN Port"
set [find name=ether2] name=ether2-lan comment="Hotspot LAN Port 2"
set [find name=ether3] name=ether3-lan comment="Hotspot LAN Port 3"
set [find name=ether4] name=ether4-lan comment="Hotspot LAN Port 4"
set [find name=ether5] name=ether5-lan comment="Bridge 2 LAN Port 5"

# Create Bridge 1 for Hotspot (Ports 2, 3, 4)
/interface bridge add name=bridge1 comment="Hotspot Bridge"
/interface bridge port add bridge=bridge1 interface=ether2-lan
/interface bridge port add bridge=bridge1 interface=ether3-lan
/interface bridge port add bridge=bridge1 interface=ether4-lan

# Create Bridge 2 for regular LAN (Port 5)
/interface bridge add name=bridge2 comment="Regular LAN Bridge"
/interface bridge port add bridge=bridge2 interface=ether5-lan

# Configure WAN interface (DHCP client)
/ip dhcp-client
add interface=ether1-wan add-default-route=yes comment="WAN DHCP Client"

# Configure Bridge 1 IP (Hotspot)
/ip address add address=192.168.10.1/24 interface=bridge1 comment="Hotspot Network"

# Configure Bridge 2 IP (Regular LAN)
/ip address add address=192.168.20.1/24 interface=bridge2 comment="Regular LAN Network"

# Setup DHCP Server for Bridge 1 (Hotspot)
/ip pool add name=dhcp-pool1 ranges=192.168.10.100-192.168.10.200
/ip dhcp-server add name=dhcp1 interface=bridge1 address-pool=dhcp-pool1 disabled=no
/ip dhcp-server network add address=192.168.10.0/24 gateway=192.168.10.1 dns-server=8.8.8.8,8.8.4.4

# Setup DHCP Server for Bridge 2 (Regular LAN)
/ip pool add name=dhcp-pool2 ranges=192.168.20.100-192.168.20.200
/ip dhcp-server add name=dhcp2 interface=bridge2 address-pool=dhcp-pool2 disabled=no
/ip dhcp-server network add address=192.168.20.0/24 gateway=192.168.20.1 dns-server=8.8.8.8,8.8.4.4

# Configure NAT for both networks
/ip firewall nat
add chain=srcnat out-interface=ether1-wan src-address=192.168.10.0/24 action=masquerade comment="NAT for Hotspot"
add chain=srcnat out-interface=ether1-wan src-address=192.168.20.0/24 action=masquerade comment="NAT for Regular LAN"

# Configure Hotspot on Bridge 1
/ip hotspot
add name=hotspot1 interface=bridge1 address-pool=dhcp-pool1 profile=hsprof1

/ip hotspot profile
add name=hsprof1 hotspot-address=192.168.10.1 dns-name="hoteldonzebe.local" html-directory=hotspot

/ip hotspot user profile
add name=default idle-timeout=30m keepalive-timeout=2m status-autorefresh=1m shared-users=1

# Add hotel hotspot users
/ip hotspot user add name="hoteldonzebe" password="Donzebe@2024" profile=default comment="Hotel Donzebe Admin"
/ip hotspot user add name="guest" password="guest123" profile=default comment="Guest User"
/ip hotspot user add name="room101" password="room101" profile=default comment="Room 101"
/ip hotspot user add name="room102" password="room102" profile=default comment="Room 102"
/ip hotspot user add name="room103" password="room103" profile=default comment="Room 103"

# Configure firewall rules for security and hotspot authentication
/ip firewall filter
add chain=forward src-address=192.168.10.0/24 dst-address=192.168.20.0/24 action=accept comment="Allow inter-network communication"
add chain=forward src-address=192.168.20.0/24 dst-address=192.168.10.0/24 action=accept comment="Allow inter-network communication"
add chain=forward connection-state=established,related action=accept comment="Allow established connections"
add chain=forward connection-state=invalid action=drop comment="Drop invalid connections"

# Block unauthorized hotspot access (require login)
/ip firewall filter
add chain=forward src-address=192.168.10.0/24 connection-state=new action=jump jump-target=hotspot-auth comment="Jump to hotspot auth check"
add chain=hotspot-auth src-address=192.168.10.0/24 dst-address=!192.168.10.1 protocol=tcp dst-port=80 action=reject reject-with=tcp-reset comment="Block HTTP without auth"
add chain=hotspot-auth src-address=192.168.10.0/24 dst-address=!192.168.10.1 protocol=tcp dst-port=443 action=reject reject-with=tcp-reset comment="Block HTTPS without auth"
add chain=hotspot-auth src-address=192.168.10.0/24 dst-address=!192.168.10.1 protocol=udp dst-port=53 action=accept comment="Allow DNS for hotspot login"
add chain=hotspot-auth src-address=192.168.10.0/24 dst-address=192.168.10.1 action=accept comment="Allow hotspot portal access"

# Enable forwarding between interfaces
/ip settings set allow-fastpath=yes
/ip firewall connection tracking set enabled=yes

# Save configuration
/system scheduler add name="save-config" start-time=startup interval=1d on-event="/system backup save name=[/system clock get date]"

# Display configuration summary
:log info "MikroTik RB951 Configuration Complete"
:log info "WAN: ether1-wan (DHCP)"
:log info "Hotspot: bridge1 (192.168.10.1/24) - Ports 2,3,4 - Hotel Donzebe"
:log info "Regular LAN: bridge2 (192.168.20.1/24) - Port 5"
:log info "Hotspot Users: hoteldonzebe, guest, room101, room102, room103"
