<?php
error_reporting(0);
set_time_limit(0);

class WexScan {
    public $device = null;
    public $problemas = [];
    public $verificacoes = 0;
    public $avisos = 0;
    
    function __construct() {
        $this->banner();
        $this->verificarADB();
        $this->conectarDispositivo();
        $this->scanCompleto();
        $this->resumoFinal();
    }
    
    function banner() {
        system('clear');
        echo "
╔══════════════════════════════════════════╗
║                                          ║
║    ██╗    ██╗███████╗██╗  ██╗           ║
║    ██║    ██║██╔════╝╚██╗██╔╝           ║
║    ██║ █╗ ██║█████╗   ╚███╔╝            ║
║    ██║███╗██║██╔══╝   ██╔██╗            ║
║    ╚███╔███╔╝███████╗██╔╝ ██╗           ║
║     ╚══╝╚══╝ ╚══════╝╚═╝  ╚═╝           ║
║                                          ║
║     Scanner de Segurança Android        ║
║           by: WexSS                    ║
║                                          ║
╚══════════════════════════════════════════╝\n\n";
    }
    
    function verificarADB() {
        echo "[0] Conectar ADB (Pareamento e conexão via ADB)\n";
        echo "────────────────────────────────────────────────\n\n";
        
        echo "[*] Verificando ADB...\n";
        $check = shell_exec("which adb");
        if(empty($check)) {
            echo "[!] Instalando android-tools...\n";
            system("pkg install android-tools -y");
            echo "[✓] ADB instalado com sucesso\n";
        } else {
            echo "[✓] ADB OK\n";
        }
    }
    
    function conectarDispositivo() {
        echo "\n[*] Pareando dispositivo...\n";
        system("adb pair localhost:");
        
        echo "\n[*] Conectando dispositivo...\n";
        system("adb connect localhost:");
        
        $dispositivos = shell_exec("adb devices");
        if(strpos($dispositivos, "device") !== false && strpos($dispositivos, "offline") === false) {
            echo "[✓] Dispositivo conectado com sucesso\n";
        } else {
            echo "[✗] Falha na conexão\n";
            exit;
        }
    }
    
    function scanCompleto() {
        echo "\n══════════════════════════════════════════\n";
        echo "    INICIANDO SCAN DE SEGURANÇA\n";
        echo "══════════════════════════════════════════\n\n";
        
        $this->checkVersao();
        $this->checkRoot();
        $this->checkBoot();
        $this->checkSELinux();
        $this->checkProps();
        $this->checkMagisk();
        $this->checkKernelSU();
        $this->checkAPatch();
        $this->checkHooks();
        $this->checkShell();
        $this->checkDiretorios();
        $this->checkProcessos();
        $this->checkRede();
        $this->checkArquivosTemp();
        $this->checkDNS();
        $this->checkVPN();
        $this->checkProxy();
        $this->checkMDM();
        $this->checkPermissoes();
        $this->checkAppsSuspeitos();
        $this->checkConfigUSB();
        $this->checkFuso();
        $this->checkFreeFire();
        $this->checkOBB();
        $this->checkGameAssets();
    }
    
    function checkVersao() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO VERSÃO DO ANDROID\n";
        echo "-----------------------------------\n";
        $versao = shell_exec("adb shell getprop ro.build.version.release");
        echo "[+] Versão do Android: " . trim($versao) . "\n\n";
    }
    
    function checkRoot() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO ACESSO ROOT\n";
        echo "-----------------------------------\n";
        
        $root = shell_exec("adb shell su -c 'id' 2>&1");
        if(strpos($root, "uid=0") !== false) {
            echo "[✗] Root DETECTADO!\n";
            $this->problemas[] = "Root detectado";
            $this->avisos++;
        } else {
            echo "[✓] Dispositivo sem root\n";
        }
        
        $su = shell_exec("adb shell which su 2>&1");
        if(strpos($su, "/su") !== false) {
            echo "[✗] Binário SU encontrado\n";
            $this->problemas[] = "Binário SU presente";
            $this->avisos++;
        } else {
            echo "[✓] Nenhum binário SU encontrado\n";
        }
        echo "\n";
    }
    
    function checkBoot() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO ESTADO DE BOOT\n";
        echo "-----------------------------------\n";
        
        $boot = shell_exec("adb shell getprop ro.boot.verifiedbootstate 2>&1");
        if(strpos($boot, "green") !== false || strpos($boot, "orange") === false) {
            echo "[✓] Boot State: GREEN\n";
        } elseif(strpos($boot, "orange") !== false) {
            echo "[✗] Boot State: ORANGE - Bootloader desbloqueado!\n";
            $this->problemas[] = "Bootloader desbloqueado";
            $this->avisos++;
        } else {
            echo "[✓] Boot State: GREEN (padrão)\n";
        }
        echo "\n";
    }
    
    function checkSELinux() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO STATUS DO SELINUX\n";
        echo "-----------------------------------\n";
        
        $selinux = shell_exec("adb shell getenforce 2>&1");
        if(strpos($selinux, "Enforcing") !== false) {
            echo "[✓] SELinux: ENFORCING\n";
        } elseif(strpos($selinux, "Permissive") !== false) {
            echo "[✗] SELinux: PERMISSIVE - Modo inseguro!\n";
            $this->problemas[] = "SELinux em modo permissivo";
            $this->avisos++;
        } else {
            echo "[!] Não foi possível verificar SELinux\n";
        }
        echo "\n";
    }
    
    function checkProps() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO PROPRIEDADES DO SISTEMA\n";
        echo "-----------------------------------\n";
        
        $usb = shell_exec("adb shell settings get global adb_enabled 2>&1");
        if(trim($usb) == "1") {
            echo "[✗] ADB persistente ativo (risco de segurança)\n";
            $this->problemas[] = "ADB persistente ativo";
            $this->avisos++;
        }
        
        $verifier = shell_exec("adb shell getprop ro.secure 2>&1");
        if(trim($verifier) == "0") {
            echo "[✗] Verificação de segurança desativada!\n";
            $this->problemas[] = "Verificação de segurança desativada";
            $this->avisos++;
        }
        
        echo "[✓] Verificação de propriedades concluída\n\n";
    }
    
    function checkMagisk() {
        $this->verificacoes++;
        echo "[►] DETECÇÃO DE MAGISK\n";
        echo "-----------------------------------\n";
        
        $magisk = shell_exec("adb shell pm list packages | grep magisk 2>&1");
        if(strpos($magisk, "magisk") !== false) {
            echo "[✗] Magisk detectado!\n";
            $this->problemas[] = "Magisk instalado";
            $this->avisos++;
        } else {
            echo "[✓] Nenhum vestígio de Magisk\n";
        }
        
        $magiskFile = shell_exec("adb shell ls /data/adb/magisk 2>&1");
        if(strpos($magiskFile, "No such file") === false) {
            echo "[✗] Arquivos do Magisk encontrados!\n";
            $this->problemas[] = "Arquivos do Magisk presentes";
            $this->avisos++;
        }
        echo "\n";
    }
    
    function checkKernelSU() {
        $this->verificacoes++;
        echo "[►] DETECÇÃO DE KERNELSU\n";
        echo "-----------------------------------\n";
        
        $kernel = shell_exec("adb shell ls /data/adb/ksu 2>&1");
        if(strpos($kernel, "No such file") === false) {
            echo "[✗] KernelSU detectado!\n";
            $this->problemas[] = "KernelSU instalado";
            $this->avisos++;
        } else {
            echo "[✓] Nenhum vestígio de KernelSU\n";
        }
        echo "\n";
    }
    
    function checkAPatch() {
        $this->verificacoes++;
        echo "[►] DETECÇÃO DE APATCH\n";
        echo "-----------------------------------\n";
        
        $apatch = shell_exec("adb shell ls /data/adb/apatch 2>&1");
        if(strpos($apatch, "No such file") === false) {
            echo "[✗] APatch detectado!\n";
            $this->problemas[] = "APatch instalado";
            $this->avisos++;
        } else {
            echo "[✓] Nenhum vestígio de APatch\n";
        }
        echo "\n";
    }
    
    function checkHooks() {
        $this->verificacoes++;
        echo "[►] DETECÇÃO DE FRAMEWORKS DE HOOK\n";
        echo "-----------------------------------\n";
        
        $xposed = shell_exec("adb shell pm list packages | grep xposed 2>&1");
        if(strpos($xposed, "xposed") !== false) {
            echo "[✗] Xposed Framework detectado!\n";
            $this->problemas[] = "Xposed instalado";
            $this->avisos++;
        } else {
            echo "[✓] Nenhum framework de hook\n";
        }
        
        $edxp = shell_exec("adb shell pm list packages | grep edxposed 2>&1");
        if(strpos($edxp, "edxposed") !== false) {
            echo "[✗] EdXposed detectado!\n";
            $this->problemas[] = "EdXposed instalado";
            $this->avisos++;
        }
        echo "\n";
    }
    
    function checkShell() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO FUNÇÕES SHELL\n";
        echo "-----------------------------------\n";
        
        echo "[*] Verificando scripts em segundo plano...\n";
        $scripts = shell_exec("adb shell ps | grep -E 'sh|bash|zsh' 2>&1");
        if(strpos($scripts, "sh") !== false) {
            echo "[!] Scripts shell ativos detectados\n";
        } else {
            echo "[✓] Nenhum script ativo\n";
        }
        
        echo "[✓] Verificação de shell concluída\n\n";
    }
    
    function checkDiretorios() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO ACESSO A DIRETÓRIOS\n";
        echo "-----------------------------------\n";
        
        $data = shell_exec("adb shell ls /data/ 2>&1");
        if(strpos($data, "Permission denied") !== false) {
            echo "[✓] Acesso negado - OK\n";
        } else {
            echo "[!] Acesso liberado - Verifique\n";
        }
        echo "\n";
    }
    
    function checkProcessos() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO PROCESSOS SUSPEITOS\n";
        echo "-----------------------------------\n";
        
        $suspects = ['frida', 'gdbserver', 'lldb', 'gdb', 'ptrace'];
        foreach($suspects as $proc) {
            $check = shell_exec("adb shell ps | grep $proc 2>&1");
            if(!empty($check) && strpos($check, "grep") === false) {
                echo "[✗] Processo suspeito: $proc\n";
                $this->problemas[] = "Processo $proc detectado";
                $this->avisos++;
            }
        }
        
        if($this->avisos == 0) {
            echo "[✓] Nenhum processo suspeito\n";
        }
        echo "\n";
    }
    
    function checkRede() {
        $this->verificacoes++;
        echo "[►] VERIFICAÇÃO DE REDE\n";
        echo "-----------------------------------\n";
        
        $dns = shell_exec("adb shell settings get global private_dns_mode 2>&1");
        if(trim($dns) == "hostname") {
            echo "[⚠] DNS Privado Ativo (hostname)\n";
        } else {
            echo "[✓] DNS padrão\n";
        }
        echo "\n";
    }
    
    function checkArquivosTemp() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO ARQUIVOS EM /DATA/LOCAL/TMP\n";
        echo "-----------------------------------\n";
        
        $tmp = shell_exec("adb shell ls -la /data/local/tmp/ 2>&1");
        $arquivos = explode("\n", $tmp);
        
        $suspeitos = [
            'brevent' => 'Brevent Script',
            'shizuku' => 'Shizuku',
            'frida' => 'Frida Server',
            'magisk' => 'Magisk',
            'ksu' => 'KernelSU',
            'zygisk' => 'Zygisk',
            'dobby' => 'Dobby',
            'gcore' => 'GameGuardian',
            'x8' => 'X8 Sandbox',
            'gg' => 'GameGuardian'
        ];
        
        $encontrados = false;
        foreach($arquivos as $arquivo) {
            foreach($suspeitos as $nome => $desc) {
                if(strpos($arquivo, $nome) !== false) {
                    echo "[✗] DETECTADO: $nome -> $desc\n";
                    $this->problemas[] = "$desc detectado";
                    $this->avisos++;
                    $encontrados = true;
                }
            }
        }
        
        if(!$encontrados) {
            echo "[✓] Nenhum arquivo suspeito encontrado\n";
        }
        echo "\n";
    }
    
    function checkDNS() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO DNS PRIVADO\n";
        echo "-----------------------------------\n";
        
        $dns = shell_exec("adb shell settings get global private_dns_mode 2>&1");
        if(trim($dns) == "hostname") {
            echo "[⚠] DNS Privado Ativo\n";
        } else {
            echo "[✓] DNS padrão\n";
        }
        echo "\n";
    }
    
    function checkVPN() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO VPN ATIVA\n";
        echo "-----------------------------------\n";
        
        $vpn = shell_exec("adb shell dumpsys connectivity | grep -i vpn 2>&1");
        if(strpos($vpn, "NetworkAgentInfo{") !== false && strpos($vpn, "VPN") !== false) {
            echo "[✗] VPN ativa detectada!\n";
            $this->problemas[] = "VPN ativa";
            $this->avisos++;
        } else {
            echo "[✓] Nenhuma VPN ativa\n";
        }
        echo "\n";
    }
    
    function checkProxy() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO PROXY\n";
        echo "-----------------------------------\n";
        
        $proxy = shell_exec("adb shell settings get global http_proxy 2>&1");
        if(!empty($proxy) && trim($proxy) != ":0" && trim($proxy) != "null") {
            echo "[✗] Proxy configurado: " . trim($proxy) . "\n";
            $this->problemas[] = "Proxy configurado";
            $this->avisos++;
        } else {
            echo "[✓] Nenhum proxy configurado\n";
        }
        echo "\n";
    }
    
    function checkMDM() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO PERFIS MDM\n";
        echo "-----------------------------------\n";
        
        $mdm = shell_exec("adb shell dumpsys device_policy 2>&1");
        if(strpos($mdm, "active admin") !== false) {
            echo "[✗] Perfil MDM ativo detectado!\n";
            $this->problemas[] = "Perfil MDM ativo";
            $this->avisos++;
        } else {
            echo "[✓] Nenhum perfil MDM\n";
        }
        echo "\n";
    }
    
    function checkPermissoes() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO PERMISSÕES SUSPEITAS\n";
        echo "-----------------------------------\n";
        
        $acessibilidade = shell_exec("adb shell settings get secure accessibility_enabled 2>&1");
        if(trim($acessibilidade) == "1") {
            echo "[⚠] Acessibilidade ativada\n";
        }
        
        $origem = shell_exec("adb shell settings get secure install_non_market_apps 2>&1");
        if(trim($origem) == "1") {
            echo "[⚠] Apps de fontes desconhecidas permitido\n";
        }
        echo "\n";
    }
    
    function checkAppsSuspeitos() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO APPS SUSPEITOS\n";
        echo "-----------------------------------\n";
        
        $cheats = [
            'com.android.gameguardian' => 'GameGuardian',
            'com.cheatengine' => 'Cheat Engine',
            'com.digibites.calc' => 'Calculator (GameGuardian)',
            'com.x8sb1' => 'X8 Sandbox',
            'com.gema.android' => 'GEMA',
            'com.leo.sandbox' => 'Leo Sandbox',
            'org.virtualbox' => 'VirtualBox',
            'com.vmos' => 'VMOS',
            'com.parallel.space' => 'Parallel Space',
            'com.ludashi.dualspace' => 'Dual Space',
            'com.excelliance.dualaiapp' => 'Dual App'
        ];
        
        $encontrados = false;
        foreach($cheats as $pkg => $nome) {
            $check = shell_exec("adb shell pm list packages | grep $pkg 2>&1");
            if(strpos($check, $pkg) !== false) {
                echo "[✗] $nome detectado!\n";
                $this->problemas[] = "$nome instalado";
                $this->avisos++;
                $encontrados = true;
            }
        }
        
        if(!$encontrados) {
            echo "[✓] Nenhum app suspeito encontrado\n";
        }
        echo "\n";
    }
    
    function checkConfigUSB() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO CONFIGURAÇÃO USB\n";
        echo "-----------------------------------\n";
        
        $usb = shell_exec("adb shell getprop persist.sys.usb.config 2>&1");
        if(strpos($usb, "adb") !== false) {
            echo "[✗] ADB persistente ativo\n";
        }
        
        $debug = shell_exec("adb shell settings get global adb_enabled 2>&1");
        if(trim($debug) == "1") {
            echo "[⚠] USB Debugging ativado\n";
        }
        echo "\n";
    }
    
    function checkFuso() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO FUSO HORÁRIO\n";
        echo "-----------------------------------\n";
        
        $fuso = shell_exec("adb shell getprop persist.sys.timezone 2>&1");
        $fuso = trim($fuso);
        
        echo "[*] Fuso horário: $fuso\n";
        
        if($fuso != "America/Sao_Paulo") {
            echo "[⚠] Fuso horário diferente do padrão ($fuso)\n";
        }
        echo "\n";
    }
    
    function checkFreeFire() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO FREE FIRE\n";
        echo "-----------------------------------\n";
        
        echo "[*] Verificando Free Fire Normal...\n";
        $ff = shell_exec("adb shell pm list packages | grep com.dts.freefireth 2>&1");
        if(strpos($ff, "com.dts.freefireth") !== false) {
            echo "[✓] Free Fire instalado\n";
            
            $install = shell_exec("adb shell dumpsys package com.dts.freefireth | grep firstInstallTime 2>&1");
            if(!empty($install)) {
                echo "[*] Data de instalação: " . trim($install) . "\n";
            }
        } else {
            echo "[!] Free Fire não encontrado\n";
        }
        
        echo "\n[*] Verificando Free Fire Max...\n";
        $ffmax = shell_exec("adb shell pm list packages | grep com.dts.freefiremax 2>&1");
        if(strpos($ffmax, "com.dts.freefiremax") !== false) {
            echo "[✓] Free Fire Max instalado\n";
            
            $install = shell_exec("adb shell dumpsys package com.dts.freefiremax | grep firstInstallTime 2>&1");
            if(!empty($install)) {
                echo "[*] Data de instalação: " . trim($install) . "\n";
            }
        } else {
            echo "[!] Free Fire Max não encontrado\n";
        }
        echo "\n";
    }
    
    function checkOBB() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO PASTA OBB\n";
        echo "-----------------------------------\n";
        
        $obb = shell_exec("adb shell ls /storage/emulated/0/Android/obb/com.dts.freefireth/ 2>&1");
        if(strpos($obb, "No such file") !== false || strpos($obb, "Permission denied") !== false) {
            echo "[⚠] OBB não encontrada ou sem acesso\n";
        } else {
            echo "[✓] OBB encontrada\n";
            
            $mod = shell_exec("adb shell ls -la /storage/emulated/0/Android/obb/com.dts.freefireth/ 2>&1");
            if(strpos($mod, "Jan 2026") !== false || strpos($mod, "Feb 2026") !== false) {
                echo "[⚠] OBB modificada recentemente\n";
            }
        }
        echo "\n";
    }
    
    function checkGameAssets() {
        $this->verificacoes++;
        echo "[►] VERIFICANDO GAME ASSETS\n";
        echo "-----------------------------------\n";
        
        $assets = shell_exec("adb shell ls -la /storage/emulated/0/Android/data/com.dts.freefireth/files/AssetBundles/ 2>&1");
        if(strpos($assets, "No such file") === false && strpos($assets, "Permission denied") === false) {
            echo "[*] Pasta de assets encontrada\n";
            
            $data = shell_exec("adb shell ls -la /storage/emulated/0/Android/data/com.dts.freefireth/files/AssetBundles/ 2>&1 | head -5");
            echo "[*] Última modificação: " . substr($assets, 0, 100) . "...\n";
        }
        echo "\n";
    }
    
    function resumoFinal() {
        echo "══════════════════════════════════════════\n";
        echo "         RESUMO DA ANÁLISE\n";
        echo "══════════════════════════════════════════\n\n";
        
        echo "Total de verificações realizadas: " . $this->verificacoes . "\n";
        
        if($this->avisos > 0) {
            echo "Problemas encontrados: " . $this->avisos . "\n\n";
            echo "⚠️  ATENÇÃO: MODIFICAÇÕES DETECTADAS! ⚠️\n";
            echo "──────────────────────────────────────\n";
            
            foreach($this->problemas as $problema) {
                echo "• " . $problema . "\n";
            }
        } else {
            echo "✓ Nenhum problema encontrado\n";
            echo "✓ Dispositivo limpo e seguro\n";
        }
        
        echo "\n══════════════════════════════════════════\n";
        echo "      SCAN FINALIZADO - WexScan v2.0\n";
        echo "══════════════════════════════════════════\n";
    }
}

$scan = new WexScan();
?>
```
