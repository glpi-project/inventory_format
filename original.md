Original XML specification
==========================

```XML
<!ELEMENT REQUEST (CONTENT, DEVICEID, QUERY)>

  <!ELEMENT CONTENT (ACCESSLOG, BIOS, HARDWARE, OPERATINGSYSTEM, ANTIVIRUS+,
  BATTERIES+, CONTROLLERS+, CPUS+, DRIVES+, ENVS+, FIREWALL, INPUTS+, LICENSEINFOS+,
  LOCAL_GROUPS+, LOCAL_USERS+, LOGICAL_VOLUMES+, MEMORIES+, MODEMS+, FIRMWARES+,
  MONITORS+, NETWORKS+, PHYSICAL_VOLUMES+, PORTS+, PRINTERS+, PROCESSES+, SLOTS+,
  SOFTWARES+, USERS+, STORAGES+, VIDEOS+, SOUNDS+, VIRTUALMACHINES+, USBDEVICES+,
  VOLUME_GROUPS+, REMOTE_MGMT+, SIMCARDS+, SENSORS+, POWERSUPPLIES+, REGISTRY,
  RUDDER, CAMERAS+)>

     <!ELEMENT BIOS (SMODEL, SMANUFACTURER, SSN, BDATE, BVERSION,
     BMANUFACTURER, MMANUFACTURER, MSN, MMODEL, ASSETTAG, ENCLOSURESERIAL,
     BIOSSERIAL, TYPE, SKUNUMBER)>
       <!-- System model (string) -->
       <!ELEMENT SMODEL (#PCDATA)>
       <!-- System manufacturer (string) -->
       <!ELEMENT SMANUFACTURER (#PCDATA)>
       <!-- System Serial number (string) -->
       <!ELEMENT SSN (#PCDATA)>
       <!-- BIOS release date, in MM/DD/YYYY format -->
       <!ELEMENT BDATE (#PCDATA)>
       <!-- BIOS version -->
       <!ELEMENT BVERSION (#PCDATA)>
       <!-- BIOS manufacturer -->
       <!ELEMENT BMANUFACTURER (#PCDATA)>
       <!ELEMENT BIOSSERIAL (#PCDATA)>
       <!-- Motherboard manufacturer -->
       <!ELEMENT MMANUFACTURER (#PCDATA)>
       <!-- Motherboard Serial number -->
       <!ELEMENT MSN (#PCDATA)>
       <!-- Motherboard model -->
       <!ELEMENT MMODEL (#PCDATA)>
       <!-- asset tag -->
       <!ELEMENT ASSETTAG (#PCDATA)>
       <!ELEMENT ENCLOSURESERIAL (#PCDATA)>
       <!-- obsoleted by HARDWARE/CHASSIS_TYPE -->
       <!ELEMENT TYPE (#PCDATA)>
       <!ELEMENT SKUNUMBER (#PCDATA)>

     <!ELEMENT HARDWARE (USERID, OSVERSION, PROCESSORN, OSCOMMENTS, CHECKSUM,
     PROCESSORT, NAME, PROCESSORS, SWAP, ETIME, TYPE, OSNAME, IPADDR,
     WORKGROUP, DESCRIPTION, MEMORY, UUID, DNS, LASTLOGGEDUSER,
     USERDOMAIN, DATELASTLOGGEDUSER, DEFAULTGATEWAY, VMSYSTEM, WINOWNER,
     WINPRODID, WINPRODKEY, WINCOMPANY, WINLANG, CHASSIS_TYPE, VMNAME,
     VMHOSTSERIAL, ARCHNAME)>
       <!-- current user list ('/' as delimiter, obsoleted by USERS) -->
       <!ELEMENT USERID (#PCDATA)>
       <!-- operating system version number (obsoleted by
          OPERATINGSYSTEM/VERSION and OPERATINGSYSTEM/KERNEL_VERSION) -->
       <!ELEMENT OSVERSION (#PCDATA)>
       <!-- operating system revision number (Service Pack on Windows, kernel
          build date on Linux) -->
       <!ELEMENT OSCOMMENTS (#PCDATA)>
       <!-- operating system full name (obsoleted by OPERATINGSYSTEM/NAME or
          OPERATINGSYSTEM/FULL_NAME) -->
       <!ELEMENT OSNAME (#PCDATA)>
       <!-- deprecated -->
       <!ELEMENT CHECKSUM (#PCDATA)>
       <!-- obsoleted by CPUS -->
       <!ELEMENT PROCESSORT (#PCDATA)>
       <!-- obsoleted by CPUS -->
       <!ELEMENT PROCESSORN (#PCDATA)>
       <!-- obsoleted by CPUS -->
       <!ELEMENT PROCESSORS (#PCDATA)>

       <!ELEMENT NAME (#PCDATA)>
       <!-- swap memory in MB -->
       <!ELEMENT SWAP (#PCDATA)>
       <!-- Total system memory in MB -->
       <!ELEMENT MEMORY (#PCDATA)>
       <!-- time needed to run the inventory -->
       <!ELEMENT ETIME (#PCDATA)>
       <!ELEMENT TYPE (#PCDATA)>
       <!-- computer chassis format, ie 'Notebook', 'Laptop', 'Server', etc.
          -->
       <!ELEMENT CHASSIS_TYPE (#PCDATA)>
       <!ELEMENT IPADDR (#PCDATA)>
       <!ELEMENT WORKGROUP (#PCDATA)>
       <!-- computer description -->
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- computer unique identifier -->
       <!ELEMENT UUID (#PCDATA)>
       <!ELEMENT DNS (#PCDATA)>
       <!-- last logged user login -->
       <!ELEMENT LASTLOGGEDUSER (#PCDATA)>
       <!-- obsoleted by USERS -->
       <!ELEMENT USERDOMAIN (#PCDATA)>
       <!ELEMENT DATELASTLOGGEDUSER (#PCDATA)>
       <!ELEMENT DEFAULTGATEWAY (#PCDATA)>
       <!-- virtualization technology
          (Physical|Xen|VirtualBox|Virtual Machine|VMware|QEMU|SolarisZone|
          VServer|OpenVZ|BSDJail|Parallels|Hyper-V|AIX_LPAR)
          -->
       <!ELEMENT VMSYSTEM (#PCDATA)>
       <!-- virtual machine name the hypervisor (VM only) -->
       <!ELEMENT VMNAME (#PCDATA)>
       <!ELEMENT WINOWNER (#PCDATA)>
       <!ELEMENT WINPRODID (#PCDATA)>
       <!ELEMENT WINPRODKEY (#PCDATA)>
       <!ELEMENT WINCOMPANY (#PCDATA)>
       <!-- Language code (Windows only) -->
       <!ELEMENT WINLANG (#PCDATA)>
       <!ELEMENT VMHOSTSERIAL (#PCDATA)>
       <!ELEMENT ARCHNAME (#PCDATA)>

     <!ELEMENT OPERATINGSYSTEM (KERNEL_NAME, KERNEL_VERSION, NAME, VERSION,
     FULL_NAME, SERVICE_PACK, INSTALL_DATE, FQDN, DNS_DOMAIN, SSH_KEY, ARCH,
     BOOT_TIME, TIMEZONE, HOSTID)>
       <!-- kernel name, ie 'freebsd', 'linux', 'hpux', 'win32', etc. -->
       <!ELEMENT KERNEL_NAME (#PCDATA)>
       <!-- kernel version, ie '2.6.32' on Linux, '5.2.x.y' on Windows
          Server 2003, etc. -->
       <!ELEMENT KERNEL_VERSION (#PCDATA)>
       <!-- operating system name ("Distributor ID" in LSB terms), ie
          'Debian', 'Ubuntu', 'CentOS', 'SUSE LINUX', 'Windows', 'OS X',
          'FreeBSD', 'AIX', 'Android', etc. -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- operating system full name, ie 'Debian GNU/Linux unstable (sid)',
          'Microsoft(R) Windows(R) Server 2003, Enterprise Edition x64', etc.
          -->
       <!ELEMENT FULL_NAME (#PCDATA)>
       <!-- operating system version ("Release" in LSB terms), ie '11.04' on
          Ubuntu natty, '5.0.8' on Debian Lenny, '5.4' on CentOS 5.4, '2003'
          for Windows Server 2003, etc. -->
       <!ELEMENT VERSION (#PCDATA)>
       <!-- operating system minor version -->
       <!ELEMENT SERVICE_PACK (#PCDATA)>
       <!-- operating system installation date -->
       <!ELEMENT INSTALL_DATE (#PCDATA)>
       <!ELEMENT FQDN (#PCDATA)>
       <!ELEMENT DNS_DOMAIN (#PCDATA)>
       <!ELEMENT HOSTID (#PCDATA)>
       <!ELEMENT SSH_KEY (#PCDATA)>
       <!-- operating system architecture -->
       <!ELEMENT ARCH (#PCDATA)>
       <!-- computer boot date, ie '2012-12-09 15:58:20' -->
       <!ELEMENT BOOT_TIME (#PCDATA)>
       <!-- operating system time zone information-->
       <!ELEMENT TIMEZONE (#PCDATA)>
          <!-- time zone name, ie 'Europe/Paris' or 'CEST' -->
          <!ELEMENT NAME (#PCDATA)>
          <!-- time zone offset to UTC, ie '+0200' -->
          <!ELEMENT OFFSET (#PCDATA)>

     <!ELEMENT ACCESSLOG (USERID, LOGDATE)>
       <!ELEMENT USERID (#PCDATA)>
       <!ELEMENT LOGDATE (#PCDATA)>

     <!ELEMENT ANTIVIRUS (COMPANY, NAME, GUID, ENABLED, UPTODATE, VERSION,
     EXPIRATION, BASE_CREATION, BASE_VERSION)>
       <!-- company name -->
       <!ELEMENT COMPANY (#PCDATA)>
       <!ELEMENT NAME (#PCDATA)>
       <!-- unique ID -->
       <!ELEMENT GUID (#PCDATA)>
       <!-- wether the antivirus is enabled (0|1) -->
       <!ELEMENT ENABLED (#PCDATA)>
       <!-- wether the antivirus is up to date (0|1) -->
       <!ELEMENT UPTODATE (#PCDATA)>
       <!ELEMENT VERSION (#PCDATA)>
       <!-- License expiration date, in DD/MM/YYYY format -->
       <!ELEMENT EXPIRATION (#PCDATA)>
       <!-- signatures base creation -->
       <!ELEMENT BASE_CREATION (#PCDATA)>
       <!-- signatures base version -->
       <!ELEMENT BASE_VERSION (#PCDATA)>

     <!ELEMENT BATTERIES (CAPACITY, CHEMISTRY, DATE, NAME, SERIAL,
     MANUFACTURER, VOLTAGE)>
       <!ELEMENT CHEMISTRY (#PCDATA)>
       <!-- manufacturing date, in DD/MM/YYYY format -->
       <!ELEMENT DATE (#PCDATA)>
       <!-- battery name -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- battery serial number -->
       <!ELEMENT SERIAL (#PCDATA)>
       <!-- battery manufacturer -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!-- battery max capacity (the maximum capacity by design), in mWh -->
       <!ELEMENT CAPACITY (#PCDATA)>
       <!-- battery real max capacity (decreases as the battery gets old), in mWh -->
       <!ELEMENT REAL_CAPACITY (#PCDATA)>
       <!-- battery voltage, in mV -->
       <!ELEMENT VOLTAGE (#PCDATA)>

     <!ELEMENT CONTROLLERS (CAPTION, DRIVER, NAME, MANUFACTURER, PCICLASS,
     VENDORID, PRODUCTID, PCISUBSYSTEMID, PCISLOT, TYPE, REV)>
       <!ELEMENT DRIVER (#PCDATA)>
       <!-- device name -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- device caption -->
       <!ELEMENT CAPTION (#PCDATA)>
       <!-- manifacturer name -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!-- device model -->
       <!ELEMENT MODEL (#PCDATA)>
       <!-- device serial number -->
       <!ELEMENT SERIAL (#PCDATA)>
       <!-- device PCI class ID -->
       <!ELEMENT PCICLASS (#PCDATA)>
       <!-- device PCI vendor ID -->
       <!ELEMENT VENDORID (#PCDATA)>
       <!-- device PCI product ID -->
       <!ELEMENT PRODUCTID (#PCDATA)>
       <!-- device PCI subsystem ID, ie '8086:2a40' -->
       <!ELEMENT PCISUBSYSTEMID (#PCDATA)>
       <!-- device PCI slot, ie '00:02.1' -->
       <!ELEMENT PCISLOT (#PCDATA)>
       <!ELEMENT TYPE (#PCDATA)>
       <!-- controller revision, ie 'rev 02' -->
       <!ELEMENT REV (#PCDATA)>

     <!ELEMENT CPUS (CACHE, CORE, DESCRIPTION, MANUFACTURER, NAME, THREAD,
     SERIAL, STEPPING, FAMILYNAME, FAMILYNUMBER, MODEL, SPEED, ID,
     EXTERNAL_CLOCK, ARCH, CORECOUNT)>
       <!-- total CPU cache size, in KB -->
       <!ELEMENT CACHE (#PCDATA)>
       <!-- cores number -->
       <!ELEMENT CORE (#PCDATA)>
       <!-- total available cores,as CORE only reports enabled count -->
       <!ELEMENT CORECOUNT (#PCDATA)>
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- CPU manufacturer, ie 'Intel', 'AMD', etc. -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!-- CPU name, ie 'Intel(R) Core(TM)2 Duo CPU P8600  @ 2.40GHz' -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- threads per core number -->
       <!ELEMENT THREAD (#PCDATA)>
       <!-- CPU serial number -->
       <!ELEMENT SERIAL (#PCDATA)>
       <!-- stepping value -->
       <!ELEMENT STEPPING (#PCDATA)>
       <!-- family name -->
       <!ELEMENT FAMILYNAME (#PCDATA)>
       <!-- family value -->
       <!ELEMENT FAMILYNUMBER (#PCDATA)>
       <!-- model value -->
       <!ELEMENT MODEL (#PCDATA)>
       <!-- CPU frequency, in MHz -->
       <!ELEMENT SPEED (#PCDATA)>
       <!-- CPU ID -->
       <!ELEMENT ID (#PCDATA)>
       <!ELEMENT EXTERNAL_CLOCK (#PCDATA)>
       <!-- CPU architecture
          (MIPS|MIPS64|Alpha|SPARC|SPARC64|m68k|i386|x86_64|PowerPC|
          PowerPC64|ARM|AArch64)
          -->
       <!ELEMENT ARCH (#PCDATA)>

     <!-- a filesystem -->
     <!ELEMENT DRIVES (CREATEDATE, DESCRIPTION, FREE, FILESYSTEM, LABEL,
     LETTER, SERIAL, SYSTEMDRIVE, TOTAL, TYPE, VOLUMN, ENCRYPT_NAME,
     ENCRYPT_ALGO, ENCRYPT_STATUS, ENCRYPT_TYPE)>
       <!-- creation date, in DD/MM/YYYY format -->
       <!ELEMENT CREATEDATE (#PCDATA)>
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- free space, in MB -->
       <!ELEMENT FREE (#PCDATA)>
       <!-- total space, in MB -->
       <!ELEMENT TOTAL (#PCDATA)>
       <!-- filesystem type, ie 'ext3' -->
       <!ELEMENT FILESYSTEM (#PCDATA)>
       <!-- filesystem user name -->
       <!ELEMENT LABEL (#PCDATA)>
       <!-- filesystem system name, ie '/dev/sda1' or 'host:/path' for NFS
          -->
       <!ELEMENT VOLUMN (#PCDATA)>
       <!-- filesystem letter (Windows only) -->
       <!ELEMENT LETTER (#PCDATA)>
       <!-- filesystem serial number or UUID -->
       <!ELEMENT SERIAL (#PCDATA)>
       <!-- wether it is the system partition (0|1) (Windows only) -->
       <!ELEMENT SYSTEMDRIVE (#PCDATA)>
       <!-- filesystem mount point (Unix only) -->
       <!ELEMENT TYPE (#PCDATA)>
       <!-- supported encryption: BitLocker, cryptsetup, FileVault, ... -->
       <!ELEMENT ENCRYPT_NAME (#PCDATA)>
       <!-- encryption algorithm: AES_128, AES_256, HARDWARE_ENCRYPTION, ... -->
       <!ELEMENT ENCRYPT_ALGO (#PCDATA)>
       <!-- encryption status: Yes, No, Partially, ... -->
       <!ELEMENT ENCRYPT_STATUS (#PCDATA)>
       <!-- encryption type: hardware, software, ... -->
       <!ELEMENT ENCRYPT_TYPE (#PCDATA)>

     <!ELEMENT ENVS (KEY, VAL)>
       <!ELEMENT KEY (#PCDATA)>
       <!ELEMENT VAL (#PCDATA)>

     <!-- Firewall -->
     <!ELEMENT FIREWALL (DESCRIPTION IPADDRESS IPADDRESS6 PROFILE STATUS)>
       <!-- active connection's interface (Windows only) -->
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- active connection's ip (Windows only) -->
       <!ELEMENT IPADDRESS (#PCDATA)>
       <!-- active connection's ip v6 (Windows only) -->
       <!ELEMENT IPADDRESS6 (#PCDATA)>
       <!-- firewall profile (Windows only) -->
       <!ELEMENT PROFILE (#PCDATA)>
       <!-- firewall status -->
       <!ELEMENT STATUS (#PCDATA)>

     <!ELEMENT INPUTS (NAME, MANUFACTURER, CAPTION, DESCRIPTION, INTERFACE,
     LAYOUT, POINTINGTYPE, TYPE)>
       <!ELEMENT NAME (#PCDATA)>
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!ELEMENT CAPTION (#PCDATA)>
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!ELEMENT INTERFACE (#PCDATA)>
       <!ELEMENT LAYOUT (#PCDATA)>
       <!ELEMENT POINTINGTYPE (#PCDATA)>
       <!ELEMENT TYPE (#PCDATA)>

     <!-- a license -->
     <!ELEMENT LICENSEINFOS (NAME, FULLNAME, KEY, COMPONENTS, TRIAL, UPDATE,
     OEM, ACTIVATION_DATE, PRODUCTID)>
       <!-- name -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- full name -->
       <!ELEMENT FULLNAME (#PCDATA)>
       <!-- registration key -->
       <!ELEMENT KEY (#PCDATA)>
       <!-- components covered -->
       <!ELEMENT COMPONENTS (#PCDATA)>
       <!ELEMENT TRIAL (#PCDATA)>
       <!ELEMENT UPDATE (#PCDATA)>
       <!ELEMENT OEM (#PCDATA)>
       <!ELEMENT ACTIVATION_DATE (#PCDATA)>
       <!-- installation ID -->
       <!ELEMENT PRODUCTID (#PCDATA)>

     <!ELEMENT LOCAL_GROUPS (ID, NAME, MEMBER+)>
       <!ELEMENT NAME (#PCDATA)>
       <!ELEMENT ID (#PCDATA)>
       <!ELEMENT MEMBER (#PCDATA)>

     <!ELEMENT LOCAL_USERS (HOME, ID, LOGIN, NAME, SHELL)>
       <!ELEMENT LOGIN (#PCDATA)>
       <!ELEMENT NAME (#PCDATA)>
       <!ELEMENT ID (#PCDATA)>
       <!ELEMENT HOME (#PCDATA)>
       <!ELEMENT SHELL (#PCDATA)>

     <!-- a LVM logical volume -->
     <!ELEMENT LOGICAL_VOLUMES (LV_NAME, VG_NAME, ATTR, SIZE, LV_UUID,
     SEG_COUNT, VG_UUID)>
       <!-- name -->
       <!ELEMENT LV_NAME (#PCDATA)>
       <!-- UUID -->
       <!ELEMENT LV_UUID (#PCDATA)>
       <!-- attributes -->
       <!ELEMENT ATTR (#PCDATA)>
       <!-- size, in MB -->
       <!ELEMENT SIZE (#PCDATA)>
       <!ELEMENT SEG_COUNT (#PCDATA)>
       <!-- volume group name -->
       <!ELEMENT VG_NAME (#PCDATA)>
       <!-- volume group UUID -->
       <!ELEMENT VG_UUID (#PCDATA)>

     <!ELEMENT MEMORIES (CAPACITY, CAPTION, FORMFACTOR, REMOVABLE, PURPOSE,
     SPEED, SERIALNUMBER, TYPE, DESCRIPTION, NUMSLOTS, MEMORYCORRECTION,
     MANUFACTURER)>
       <!-- memory capacity, in MB -->
       <!ELEMENT CAPACITY (#PCDATA)>
       <!ELEMENT CAPTION (#PCDATA)>
       <!-- See Win32_PhysicalMemory documentation -->
       <!ELEMENT FORMFACTOR (#PCDATA)>
       <!ELEMENT REMOVABLE (#PCDATA)>
       <!-- See Win32_PhysicalMemory documentation -->
       <!ELEMENT PURPOSE (#PCDATA)>
       <!-- memory frequency, in Mhz -->
       <!ELEMENT SPEED (#PCDATA)>
       <!-- memory serial number -->
       <!ELEMENT SERIALNUMBER (#PCDATA)>
       <!-- memory model -->
       <!ELEMENT MODEL (#PCDATA)>
       <!ELEMENT TYPE (#PCDATA)>
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- memory slot number -->
       <!ELEMENT NUMSLOTS (#PCDATA)>
       <!ELEMENT MEMORYCORRECTION (#PCDATA)>
       <!ELEMENT MANUFACTURER (#PCDATA)>

     <!ELEMENT MODEMS (NAME, DESCRIPTION, TYPE, MODEL, MANUFACTURER, SERIAL, IMEI+)>
       <!-- modem name -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- modem description if available -->
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- modem type -->
       <!ELEMENT TYPE (#PCDATA)>
       <!-- modem model -->
       <!ELEMENT MODEL (#PCDATA)>
       <!-- modem manufacturer -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!-- modem serial -->
       <!ELEMENT SERIAL (#PCDATA)>
       <!-- IMEI information -->
       <!ELEMENT IMEI (#PCDATA)>

     <!-- component firmwares -->
     <!ELEMENT FIRMWARES (NAME, DESCRIPTION, TYPE, VERSION, DATE, MANUFACTURER)>
       <!-- component name using the firmware -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- firmware description if available -->
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- short description of firmware type: modem, bios, ... -->
       <!ELEMENT TYPE (#PCDATA)>
       <!-- full firmware version -->
       <!ELEMENT VERSION (#PCDATA)>
       <!-- firmware date -->
       <!ELEMENT DATE (#PCDATA)>
       <!-- firmware manufacturer -->
       <!ELEMENT MANUFACTURER (#PCDATA)>

     <!ELEMENT MONITORS (BASE64, CAPTION, DESCRIPTION, MANUFACTURER, SERIAL,
       ALTSERIAL?, PORT?, UUENCODE)>
       <!-- uuencoded EDID trame -->
       <!ELEMENT BASE64 (#PCDATA)>
       <!ELEMENT CAPTION (#PCDATA)>
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- monitor manufacturer -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!-- monitor serial number -->
       <!ELEMENT SERIAL (#PCDATA)>
       <!-- alternative monitor serial number computed from known manufacter
          formula and if different from SERIAL -->
       <!ELEMENT ALTSERIAL (#PCDATA)>
       <!-- Used port to connect the device -->
       <!ELEMENT PORT (#PCDATA)>
       <!-- uuencoded EDID trame -->
       <!ELEMENT UUENCODE (#PCDATA)>
       <!ELEMENT NAME (#PCDATA)>
       <!ELEMENT TYPE (#PCDATA)>

     <!-- a network configuration, ie a combination of an interface and an
       IP address -->
     <!ELEMENT NETWORKS (DESCRIPTION, MANUFACTURER, MODEL, MANAGEMENT, TYPE,
     VIRTUALDEV, MACADDR, WWN, DRIVER, FIRMWARE, PCIID, PCISLOT, PNPDEVICEID,
     MTU, SPEED, STATUS, SLAVES, BASE, IPADDRESS, IPSUBNET, IPMASK, IPDHCP,
     IPGATEWAY, IPADDRESS6, IPSUBNET6, IPMASK6, WIFI_BSSID, WIFI_SSID,
     WIFI_MODE, WIFI_VERSION)>
       <!-- interface name, ie 'eth0' on Linux, 'AMD PCNET Family Ethernet
       Adapter' on Windows -->
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- interface manufacturer -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!-- interface model -->
       <!ELEMENT MODEL (#PCDATA)>
       <!-- wether it is a remote management interface such as HP iLO, Sun
          SC, HP MP (0|1) -->
       <!ELEMENT MANAGEMENT (#PCDATA)>
       <!-- interface type
          (ethernet|wifi|infiniband|aggregate|alias|dialup|loopback|bridge|fibrechannel)
          -->
       <!ELEMENT TYPE (#PCDATA)>
       <!-- wether it is a virtual interface (0|1) -->
       <!ELEMENT VIRTUALDEV (#PCDATA)>
       <!ELEMENT MACADDR (#PCDATA)>
       <!-- World Wide Name http://fr.wikipedia.org/wiki/World_Wide_Name -->
       <!ELEMENT WWN (#PCDATA)>
       <!-- driver name -->
       <!ELEMENT DRIVER (#PCDATA)>
       <!ELEMENT FIRMWARE (#PCDATA)>
       <!-- PCI slot name -->
       <!ELEMENT PCISLOT (#PCDATA)>
       <!-- PCI device ID -->
       <!ELEMENT PCIID (#PCDATA)>
       <!-- PCI device ID (windows only) -->
       <!ELEMENT PNPDEVICEID (#PCDATA)>
       <!ELEMENT MTU (#PCDATA)>
       <!-- interface speed, in Mb/s -->
       <!ELEMENT SPEED (#PCDATA)>
       <!-- interface status (up|down) -->
       <!ELEMENT STATUS (#PCDATA)>
       <!-- list of component interfaces, for aggregate and bridges, with
          ',' as delimiter -->
       <!ELEMENT SLAVES (#PCDATA)>
       <!-- actual interface for aliases -->
       <!ELEMENT BASE (#PCDATA)>
       <!-- IPv4 address -->
       <!ELEMENT IPADDRESS (#PCDATA)>
       <!ELEMENT IPSUBNET (#PCDATA)>
       <!ELEMENT IPMASK (#PCDATA)>
       <!-- DHCP server IP address -->
       <!ELEMENT IPDHCP (#PCDATA)>
       <!-- gateway IP address -->
       <!ELEMENT IPGATEWAY (#PCDATA)>
       <!-- IPv6 address of the interface -->
       <!ELEMENT IPADDRESS6 (#PCDATA)>
       <!ELEMENT IPSUBNET6 (#PCDATA)>
       <!ELEMENT IPMASK6 (#PCDATA)>
       <!-- wifi access point name -->
       <!ELEMENT WIFI_SSID (#PCDATA)>
       <!-- wifi access point MAC address -->
       <!ELEMENT WIFI_BSSID (#PCDATA)>
       <!-- wifi mode -->
       <!ELEMENT WIFI_MODE (#PCDATA)>
       <!-- wifi protocol version -->
       <!ELEMENT WIFI_VERSION (#PCDATA)>

     <!-- a LVM physical volume -->
     <!ELEMENT PHYSICAL_VOLUMES (DEVICE, PV_PE_COUNT, PV_UUID, FORMAT, ATTR,
     SIZE, FREE, PE_SIZE, VG_UUID)>
       <!-- UUID -->
       <!ELEMENT PV_UUID (#PCDATA)>
       <!-- device name, ie '/dev/sda1' -->
       <!ELEMENT DEVICE (#PCDATA)>
       <!ELEMENT PV_PE_COUNT (#PCDATA)>
       <!-- format, ie 'lvm2' -->
       <!ELEMENT FORMAT (#PCDATA)>
       <!-- attributes -->
       <!ELEMENT ATTR (#PCDATA)>
       <!-- size, in MB -->
       <!ELEMENT SIZE (#PCDATA)>
       <!ELEMENT FREE (#PCDATA)>
       <!ELEMENT PE_SIZE (#PCDATA)>
       <!ELEMENT VG_UUID (#PCDATA)>


     <!ELEMENT PORTS (CAPTION, DESCRIPTION, NAME, TYPE)>
       <!ELEMENT CAPTION (#PCDATA)>
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!ELEMENT NAME (#PCDATA)>
       <!ELEMENT TYPE (#PCDATA)>

     <!ELEMENT PRINTERS (COMMENT, DESCRIPTION, DRIVER, NAME, NETWORK, PORT,
     RESOLUTION, SHARED, STATUS, ERRSTATUS, SERVERNAME, SHARENAME,
     PRINTPROCESSOR, SERIAL)>
       <!ELEMENT COMMENT (#PCDATA)>
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!ELEMENT DRIVER (#PCDATA)>
       <!ELEMENT NAME (#PCDATA)>
       <!-- wether it is a network printer (0|1) -->
       <!ELEMENT NETWORK (#PCDATA)>
       <!ELEMENT PORT (#PCDATA)>
       <!-- printer resolution, ie '600x600' -->
       <!ELEMENT RESOLUTION (#PCDATA)>
       <!-- wether it is a shared printer (0|1) (Windows only) -->
       <!ELEMENT SHARED (#PCDATA)>
       <!ELEMENT STATUS (#PCDATA)>
       <!ELEMENT ERRSTATUS (#PCDATA)>
       <!ELEMENT SERVERNAME (#PCDATA)>
       <!ELEMENT SHARENAME (#PCDATA)>
       <!ELEMENT PRINTPROCESSOR (#PCDATA)>
       <!-- printer serial number -->
       <!ELEMENT SERIAL (#PCDATA)>

     <!ELEMENT PROCESSES (USER, PID, CPUUSAGE, MEM, VIRTUALMEMORY, TTY,
     STARTED, CMD)>
       <!-- process owner -->
       <!ELEMENT USER (#PCDATA)>
       <!-- process ID -->
       <!ELEMENT PID (#PCDATA)>
       <!-- CPU usage -->
       <!ELEMENT CPUUSAGE (#PCDATA)>
       <!-- memory usage -->
       <!ELEMENT MEM (#PCDATA)>
       <!ELEMENT VIRTUALMEMORY (#PCDATA)>
       <!ELEMENT TTY (#PCDATA)>
       <!-- process start time, in YYYY/MM/DD HH:MM format -->
       <!ELEMENT STARTED (#PCDATA)>
       <!-- process command -->
       <!ELEMENT CMD (#PCDATA)>

     <!ELEMENT REGISTRY (NAME, REGVALUE, HIVE)>
       <!ELEMENT NAME (#PCDATA)>
       <!ELEMENT REGVALUE (#PCDATA)>
       <!ELEMENT HIVE (#PCDATA)>

     <!ELEMENT RUDDER (AGENT, UUID, HOSTNAME, SERVER_ROLES, AGENT_CAPABILITIES)>
       <!ELEMENT AGENT (#PCDATA)>
       <!ELEMENT UUID (#PCDATA)>
       <!ELEMENT HOSTNAME (#PCDATA)>
       <!ELEMENT SERVER_ROLES (#PCDATA)>
       <!ELEMENT AGENT_CAPABILITIES (#PCDATA)>

     <!-- a physical connection points including ports, motherboard slots
     and peripherals, and proprietary connection points -->
     <!ELEMENT SLOTS (DESCRIPTION, DESIGNATION, NAME, STATUS)>
       <!-- bus type -->
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!ELEMENT DESIGNATION (#PCDATA)>
       <!-- slot identifier -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- slot usage status (free|used) -->
       <!ELEMENT STATUS (#PCDATA)>

     <!ELEMENT SOFTWARES (COMMENTS, FILESIZE, FOLDER, FROM, HELPLINK,
     INSTALLDATE, NAME, NO_REMOVE, RELEASE_TYPE, PUBLISHER,
     UNINSTALL_STRING, URL_INFO_ABOUT, VERSION, VERSION_MINOR,
     VERSION_MAJOR, GUID, ARCH, USERNAME, USERID, SYSTEM_CATEGORY)>
       <!ELEMENT COMMENTS (#PCDATA)>
       <!ELEMENT FILESIZE (#PCDATA)>
       <!ELEMENT FOLDER (#PCDATA)>
       <!-- information source, ie 'registry', 'rpm', 'deb', etc. ->
       <!ELEMENT FROM (#PCDATA)>
       <!ELEMENT HELPLINK (#PCDATA)>
       <!-- installation date, in DD/MM/YYYY format -->
       <!ELEMENT INSTALLDATE (#PCDATA)>
       <!ELEMENT NAME (#PCDATA)>
       <!-- wether the software can be uninstalled (0|1) -->
       <!ELEMENT NO_REMOVE (#PCDATA)>
       <!ELEMENT RELEASE_TYPE (#PCDATA)>
       <!ELEMENT PUBLISHER (#PCDATA)>
       <!ELEMENT UNINSTALL_STRING (#PCDATA)>
       <!ELEMENT URL_INFO_ABOUT (#PCDATA)>
       <!ELEMENT VERSION (#PCDATA)>
       <!ELEMENT VERSION_MINOR (#PCDATA)>
       <!ELEMENT VERSION_MAJOR (#PCDATA)>
       <!-- software GUID (Windows only) -->
       <!ELEMENT GUID (#PCDATA)>
       <!-- software architecture -->
       <!ELEMENT ARCH (#PCDATA)>
       <!-- software owner name -->
       <!ELEMENT USERNAME (#PCDATA)>
       <!-- software owner ID -->
       <!ELEMENT USERID (#PCDATA)>
       <!-- software system category  -->
       <!ELEMENT SYSTEM_CATEGORY (#PCDATA)>

     <!ELEMENT SOUNDS (CAPTION, DESCRIPTION, MANUFACTURER, NAME)>
       <!ELEMENT CAPTION (#PCDATA)>
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!ELEMENT NAME (#PCDATA)>

     <!ELEMENT STORAGES (DESCRIPTION, DISKSIZE, INTERFACE, MANUFACTURER,
     MODEL, NAME, TYPE, SERIAL, SERIALNUMBER, FIRMWARE, SCSI_COID,
     SCSI_CHID, SCSI_UNID, SCSI_LUN, WWN, ENCRYPT_NAME, ENCRYPT_ALGO,
     ENCRYPT_STATUS, ENCRYPT_TYPE)>
       <!-- device name, ie 'hda' on Linux, '\\.\PHYSICALDRIVE0' on Windows
          -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- device description -->
       <!ELEMENT DESCRIPTION (#PCDATA)>
       <!-- device size, in MB -->
       <!ELEMENT DISKSIZE (#PCDATA)>
       <!-- device interface type (SCSI|HDC|IDE|USB|1394|serial-ATA|SAS) -->
       <!ELEMENT INTERFACE (#PCDATA)>
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!ELEMENT MODEL (#PCDATA)>
       <!ELEMENT TYPE (#PCDATA)>
       <!-- device serial number -->
       <!ELEMENT SERIAL (#PCDATA)>
       <!-- device serial number, obsoleted by SERIAL -->
       <!ELEMENT SERIALNUMBER (#PCDATA)>
       <!ELEMENT FIRMWARE (#PCDATA)>
       <!ELEMENT SCSI_COID (#PCDATA)>
       <!ELEMENT SCSI_CHID (#PCDATA)>
       <!ELEMENT SCSI_UNID (#PCDATA)>
       <!ELEMENT SCSI_LUN (#PCDATA)>
       <!-- World Wide Name http://fr.wikipedia.org/wiki/World_Wide_Name -->
       <!ELEMENT WWN (#PCDATA)>
       <!-- supported encryption name -->
       <!ELEMENT ENCRYPT_NAME (#PCDATA)>
       <!-- encryption algorithme -->
       <!ELEMENT ENCRYPT_ALGO (#PCDATA)>
       <!-- encryption status -->
       <!ELEMENT ENCRYPT_STATUS (#PCDATA)>
       <!-- encryption type -->
       <!ELEMENT ENCRYPT_TYPE (#PCDATA)>

     <!ELEMENT VIDEOS (CHIPSET, MEMORY, NAME, RESOLUTION, PCISLOT)>
       <!ELEMENT CHIPSET (#PCDATA)>
       <!-- video card memory, in MB -->
       <!ELEMENT MEMORY (#PCDATA)>
       <!ELEMENT NAME (#PCDATA)>
       <!-- video card resolution, ie '1024x768' -->
       <!ELEMENT RESOLUTION (#PCDATA)>
       <!-- video card PCI slot ID -->
       <!ELEMENT PCISLOT (#PCDATA)>

     <!ELEMENT USBDEVICES (VENDORID, PRODUCTID, MANUFACTURER, CAPTION,
     SERIAL, CLASS, SUBCLASS, NAME)>
       <!-- device USB vendor ID -->
       <!ELEMENT VENDORID (#PCDATA)>
       <!-- device USB product ID -->
       <!ELEMENT PRODUCTID (#PCDATA)>
       <!-- device manufacturer -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!-- device caption -->
       <!ELEMENT CAPTION (#PCDATA)>
       <!-- device serial number -->
       <!ELEMENT SERIAL (#PCDATA)>
       <!-- device USB class ID -->
       <!ELEMENT CLASS (#PCDATA)>
       <!-- device USB subclass ID -->
       <!ELEMENT SUBCLASS (#PCDATA)>
       <!-- device name -->
       <!ELEMENT NAME (#PCDATA)>

     <!ELEMENT USERS (LOGIN, DOMAIN)>
       <!ELEMENT LOGIN (#PCDATA)>
       <!-- user domain (Windows only) -->
       <!ELEMENT DOMAIN (#PCDATA)>

     <!ELEMENT VIRTUALMACHINES (MEMORY, NAME, UUID, STATUS, SUBSYSTEM,
     VMTYPE, VCPU, VMID, MAC, COMMENT, OWNER, SERIAL, IMAGE)>
       <!-- name -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- UUID -->
       <!ELEMENT UUID (#PCDATA)>
       <!-- status
          (running|blocked|idle|paused|shutdown|crashed|dying|off)
          -->
       <!ELEMENT STATUS (#PCDATA)>
       <!-- virtualisation software, ie 'VmWare ESX' -->
       <!ELEMENT SUBSYSTEM (#PCDATA)>
       <!- virtualisation technology
          (Physical|Xen|VirtualBox|Virtual Machine|VMware|QEMU|SolarisZone|
          VServer|OpenVZ|BSDJail|Parallels|Hyper-V|AIX_LPAR|Docker)
          -->
       <!ELEMENT VMTYPE (#PCDATA)>
       <!-- memory size, in MB -->
       <!ELEMENT MEMORY (#PCDATA)>
       <!-- CPU numbers -->
       <!ELEMENT VCPU (#PCDATA)>
       <!-- ID -->
       <!ELEMENT VMID (#PCDATA)>
       <!-- list of the MAC addresses of the virtual machine, with '/' as
          delimiter -->
       <!ELEMENT MAC (#PCDATA)>
       <!ELEMENT COMMENT (#PCDATA)>
       <!ELEMENT OWNER (#PCDATA)>
       <!ELEMENT SERIAL (#PCDATA)>
       <!-- image used for creation (Docker image for instance) -->
       <!ELEMENT IMAGE (#PCDATA)>

     <!-- a LVM logical volume group -->
     <!ELEMENT VOLUME_GROUPS (VG_NAME, PV_COUNT, LV_COUNT, ATTR, SIZE, FREE,
     VG_UUID, VG_EXTENT_SIZE)>
       <!-- name -->
       <!ELEMENT VG_NAME (#PCDATA)>
       <!-- UUID -->
       <!ELEMENT VG_UUID (#PCDATA)>
       <!ELEMENT PV_COUNT (#PCDATA)>
       <!ELEMENT LV_COUNT (#PCDATA)>
       <!-- attributes -->
       <!ELEMENT ATTR (#PCDATA)>
       <!-- size, in MB -->
       <!ELEMENT SIZE (#PCDATA)>
       <!-- free space -->
       <!ELEMENT FREE (#PCDATA)>
       <!ELEMENT VG_EXTENT_SIZE (#PCDATA)>

     <!ELEMENT REMOTE_MGMT (ID, TYPE)>
       <!-- id -->
       <!ELEMENT ID (#PCDATA)>
       <!-- type
          (teamviewer)
          -->
       <!ELEMENT TYPE (#PCDATA)>

     <!ELEMENT SIMCARDS (IMSI, PHONE_NUMBER, ICCID, STATE, COUNTRY, OPERATOR_CODE,
     OPERATOR_NAME)>
       <!-- IMSI -->
       <!ELEMENT IMSI (#PCDATA)>
       <!-- Phone number -->
       <!ELEMENT PHONE_NUMBER (#PCDATA)>
       <!-- ICCID (serial number) -->
       <!ELEMENT ICCID (#PCDATA)>
       <!-- State -->
       <!ELEMENT STATE(#PCDATA)>
       <!-- Country -->
       <!ELEMENT COUNTRY (#PCDATA)>
       <!-- Operator code -->
       <!ELEMENT OPERATOR_CODE (#PCDATA)>
       <!-- Operator Name -->
       <!ELEMENT OPERATOR_NAME(#PCDATA)>

     <!ELEMENT SENSORS (NAME, MANUFACTURER, TYPE, VERSION)>
       <!-- Name -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- MANUFACTURER -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!-- version -->
       <!ELEMENT VERSION (#PCDATA)>>
       <!-- type -->
       <!ELEMENT TYPE (#PCDATA)>>

     <!ELEMENT POWERSUPPLIES (NAME, POWER_MAX, SERIALNUMBER, PARTNUM
     MANUFACTURER, MODEL, HOTREPLACEABLE, PLUGGED, STATUS)>
       <!-- powersupply name -->
       <!ELEMENT NAME (#PCDATA)>
       <!-- powersupply max power in Watt -->
       <!ELEMENT POWER_MAX (#PCDATA)>
       <!-- powersupply serial number -->
       <!ELEMENT SERIALNUMBER (#PCDATA)>
       <!-- powersupply manufacturer -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!-- powersupply Part Number -->
       <!ELEMENT PARTNUM (#PCDATA)>
       <!-- powersupply model -->
       <!ELEMENT MODEL (#PCDATA)>
       <!-- powersupply hotreplaceable feature (Yes|No) -->
       <!ELEMENT HOTREPLACEABLE (#PCDATA)>
       <!-- powersupply plugged status (Yes|No) -->
       <!ELEMENT PLUGGED (#PCDATA)>
       <!-- powersupply status -->
       <!ELEMENT STATUS (#PCDATA)>
       <!-- powersupply location in computer -->
       <!ELEMENT LOCATION (#PCDATA)>

     <!ELEMENT CAMERAS (RESOLUTION, LENSFACING, FLASHUNIT,
     IMAGEFORMATS+, ORIENTATION, FOCALLENGHT, SENSORSIZE, MANUFACTURER,
     RESOLUTIONVIDEO, MODEL, SUPPORTS+)>
       <!-- Camera resolution, ie '1200x800' -->
       <!ELEMENT RESOLUTION (#PCDATA)>
       <!-- Direction the camera faces, relative to device screen -->
       <!ELEMENT LENSFACING (#PCDATA)>
       <!-- Whether this camera device has a flash unit -->
       <!ELEMENT FLASHUNIT (#PCDATA)>
       <!-- Image output formats -->
       <!ELEMENT IMAGEFORMATS (#PCDATA)>
       <!-- Orientation camera - Degrees of clockwise rotation -->
       <!ELEMENT ORIENTATION (#PCDATA)>
       <!-- List of focal lengths - Units in Millimeters -->
       <!ELEMENT FOCALLENGHT (#PCDATA)>
       <!-- This is the physical size of the sensor - Units in Millimeters -->
       <!ELEMENT SENSORSIZE (#PCDATA)>
       <!-- Name of the Manufacturer -->
       <!ELEMENT MANUFACTURER (#PCDATA)>
       <!--  Video Camera resolution, ie '1200x800'  -->
       <!ELEMENT RESOLUTIONVIDEO (#PCDATA)>
       <!-- Name of the Model -->
       <!ELEMENT MODEL (#PCDATA)>
       <!-- List of intefaces and protocols about Camera -->
       <!ELEMENT SUPPORTS (#PCDATA)>

  <!-- agent ID (string) -->
  <!ELEMENT DEVICEID (#PCDATA)>
  <!-- message type, ie "SNMPQUERY" -->
  <!ELEMENT QUERY (#PCDATA)>
```
