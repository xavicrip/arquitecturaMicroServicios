#!/bin/bash
echo "🚀 Iniciando Arquitectura de Microservicios..."

# Función para iniciar un servicio
start_service() {
    local service_name=$1
    local port=$2
    local dir=$3
    
    echo "📍 Iniciando $service_name en puerto $port..."
    cd "$dir" && php -S "localhost:$port" -t public > /dev/null 2>&1 &
    echo "$service_name iniciado (PID: $!)"
}

# Iniciar servicios en background
start_service "Authors API" 8001 "LumenAuthorsApi"
start_service "Books API" 8002 "LumenBooksApi"
start_service "Reviews API" 8003 "LumenReviewsApi"
start_service "Gateway API" 8000 "LumenGatewayApi"

echo ""
echo "✅ Todos los servicios iniciados!"
echo ""
echo "🌐 URLs de acceso:"
echo "   Gateway API:  http://localhost:8000"
echo "   Authors API:  http://localhost:8001"
echo "   Books API:    http://localhost:8002"
echo "   Reviews API:  http://localhost:8003"
echo ""
echo "🛑 Para detener todos los servicios: killall php"
echo ""

# Mantener el script corriendo
wait
