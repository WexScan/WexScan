import requests
import json

print("=" * 50)
print("🔍 TESTADOR DE PROXYS")
print("=" * 50)

# Lista de proxies para testar
proxies = [
    "http://proxy-suspeito.com:8080",
    "https://vpn-teste.com:443",
]

resultados = []

for proxy in proxies:
    print(f"\n🔄 Testando: {proxy}")
    
    try:
        response = requests.get(
            "https://httpbin.org/ip",
            proxies={"http": proxy, "https": proxy},
            timeout=5
        )
        
        if response.status_code == 200:
            print(f"✅ ATIVA! Respondeu em {response.elapsed.total_seconds():.2f} segundos")
            print(f"📡 IP: {response.json()}")
            resultados.append({"proxy": proxy, "status": "ativa"})
        else:
            print(f"⚠️ Respondeu com erro: {response.status_code}")
            resultados.append({"proxy": proxy, "status": "erro"})
            
    except Exception as e:
        print(f"❌ INATIVA - {str(e)[:50]}")
        resultados.append({"proxy": proxy, "status": "inativa"})

print("\n" + "=" * 50)
print("📊 RESUMO")
print("=" * 50)

ativas = [r for r in resultados if r["status"] == "ativa"]
print(f"✅ Ativas: {len(ativas)}")
print(f"❌ Inativas: {len(resultados) - len(ativas)}")

# Salvar resultados
with open("resultado.json", "w") as f:
    json.dump(resultados, f, indent=2)
    
print("\n✅ Resultados salvos em resultado.json")
