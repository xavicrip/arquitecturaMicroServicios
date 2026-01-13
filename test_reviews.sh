#!/bin/bash

# Script de prueba para el servicio de Reviews
# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== PRUEBAS DE REVIEWS API ===${NC}\n"

# Función para verificar que los servicios estén corriendo
check_services() {
    echo -e "${YELLOW}Verificando que todos los servicios estén corriendo...${NC}"

    services=(
        "Gateway:http://localhost:8000"
        "Authors:http://localhost:8001"
        "Books:http://localhost:8002"
        "Reviews:http://localhost:8003"
    )

    all_running=true
    for service in "${services[@]}"; do
        name=$(echo $service | cut -d: -f1)
        url=$(echo $service | cut -d: -f2)

        if curl -s --max-time 3 "$url" > /dev/null; then
            echo -e "   ${GREEN}✓${NC} $name ($url)"
        else
            echo -e "   ${RED}✗${NC} $name ($url) - NO DISPONIBLE"
            all_running=false
        fi
    done

    if [ "$all_running" = false ]; then
        echo -e "\n${RED}ERROR: Algunos servicios no están corriendo. Ejecuta primero:${NC}"
        echo -e "   ./start-services.sh"
        echo -e "   ./start-reviews.sh"
        exit 1
    fi

    echo -e "${GREEN}✓ Todos los servicios están corriendo${NC}\n"
}

# Función para hacer requests y mostrar resultados
test_endpoint() {
    local method=$1
    local url=$2
    local data=$3
    local description=$4

    echo -e "${YELLOW}➜ ${description}${NC}"
    echo -e "   ${method} ${url}"

    if [ -z "$data" ]; then
        response=$(curl -s -w "\nHTTP_CODE:%{http_code}" "$url" 2>&1)
    else
        response=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X "$method" -H "Content-Type: application/json" -d "$data" "$url" 2>&1)
    fi

    http_code=$(echo "$response" | grep "HTTP_CODE" | cut -d: -f2)
    body=$(echo "$response" | sed '/HTTP_CODE/d')

    # Filtrar solo JSON (ignorar warnings de PHP)
    json_response=$(echo "$body" | grep -E '^\{"data"|^\{"error"|\^\[|^\{' | head -1)

    if [ "$http_code" -ge 200 ] && [ "$http_code" -lt 300 ]; then
        echo -e "   ${GREEN}✓${NC} HTTP $http_code"
        if [ -n "$json_response" ]; then
            echo -e "   Respuesta: $json_response" | head -c 150
            echo "..."
        fi
    elif [ "$http_code" -ge 400 ] && [ "$http_code" -lt 500 ]; then
        echo -e "   ${YELLOW}⚠${NC} HTTP $http_code (Error esperado)"
        if [ -n "$json_response" ]; then
            echo -e "   Error: $json_response" | head -c 150
        fi
    else
        echo -e "   ${RED}✗${NC} HTTP $http_code (Error inesperado)"
        if [ -n "$json_response" ]; then
            echo -e "   Error: $json_response" | head -c 150
        fi
    fi
    echo ""
}

# Verificar servicios
check_services

# 1. Crear datos de prueba (autor y libro)
echo -e "${BLUE}1. CREANDO DATOS DE PRUEBA${NC}"
test_endpoint "POST" "http://localhost:8000/authors" '{"name":"Gabriel García Márquez","gender":"male","country":"Colombia"}' "Crear autor de prueba"
test_endpoint "POST" "http://localhost:8000/books" '{"title":"Cien años de soledad","description":"Novela del realismo mágico","price":2500,"author_id":1}' "Crear libro de prueba"

# 2. Probar Reviews API directamente
echo -e "${BLUE}2. PROBANDO REVIEWS API DIRECTAMENTE (Puerto 8003)${NC}"
test_endpoint "GET" "http://localhost:8003/reviews" "" "Obtener todas las reviews (debe estar vacío)"

# 3. Crear reviews válidas
echo -e "${BLUE}3. CREANDO REVIEWS VÁLIDAS${NC}"
test_endpoint "POST" "http://localhost:8003/reviews" '{"comment":"Excelente libro, una obra maestra","rating":5,"book_id":1}' "Crear review válida"
test_endpoint "POST" "http://localhost:8003/reviews" '{"comment":"Muy buena lectura","rating":4,"book_id":1}' "Crear otra review válida"

# 4. Listar reviews
echo -e "${BLUE}4. LISTANDO REVIEWS${NC}"
test_endpoint "GET" "http://localhost:8003/reviews" "" "Obtener todas las reviews"

# 5. Obtener review específica
echo -e "${BLUE}5. OBTENIENDO REVIEW ESPECÍFICA${NC}"
test_endpoint "GET" "http://localhost:8003/reviews/1" "" "Obtener review con ID 1"

# 6. Probar errores de validación
echo -e "${BLUE}6. PROBANDO ERRORES DE VALIDACIÓN${NC}"
test_endpoint "POST" "http://localhost:8003/reviews" '{"comment":"","rating":6,"book_id":1}' "Review con rating inválido (>5)"
test_endpoint "POST" "http://localhost:8003/reviews" '{"comment":"Buen libro","rating":3,"book_id":999}' "Review con libro inexistente"

# 7. Actualizar review
echo -e "${BLUE}7. ACTUALIZANDO REVIEW${NC}"
test_endpoint "PUT" "http://localhost:8003/reviews/1" '{"comment":"Obra maestra del realismo mágico","rating":5}' "Actualizar review existente"

# 8. Probar Gateway (debe funcionar igual)
echo -e "${BLUE}8. PROBANDO GATEWAY (Puerto 8000)${NC}"
test_endpoint "GET" "http://localhost:8000/reviews" "" "Obtener reviews desde Gateway"
test_endpoint "POST" "http://localhost:8000/reviews" '{"comment":"Review desde Gateway","rating":4,"book_id":1}' "Crear review desde Gateway"

# 9. Probar eliminación
echo -e "${BLUE}9. ELIMINANDO REVIEW${NC}"
test_endpoint "DELETE" "http://localhost:8003/reviews/2" "" "Eliminar review con ID 2"
test_endpoint "GET" "http://localhost:8003/reviews" "" "Verificar que se eliminó"

echo -e "${GREEN}=== PRUEBAS COMPLETADAS ===${NC}"
echo -e "Si todas las pruebas pasaron correctamente, el servicio de Reviews está funcionando! 🎉"