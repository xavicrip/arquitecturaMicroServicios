#!/bin/bash

# Script de prueba para el servicio de Ratings
# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== PRUEBAS DE RATINGS API ===${NC}\n"

# Función para verificar que los servicios estén corriendo
check_services() {
    echo -e "${YELLOW}Verificando que todos los servicios estén corriendo...${NC}"

    services=(
        "Gateway:http://localhost:8000"
        "Authors:http://localhost:8001"
        "Books:http://localhost:8002"
        "Reviews:http://localhost:8003"
        "Ratings:http://localhost:8007"
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
        echo -e "   ./start-ratings.sh"
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
test_endpoint "POST" "http://localhost:8000/authors" '{"name":"Isabel Allende","gender":"female","country":"Chile"}' "Crear segundo autor"
test_endpoint "POST" "http://localhost:8000/books" '{"title":"La casa de los espíritus","description":"Novela mágica","price":2200,"author_id":2}' "Crear segundo libro"

# 2. Pruebas de Ratings
echo -e "${BLUE}2. PRUEBAS DE RATINGS${NC}"

# Crear rating válido
test_endpoint "POST" "http://localhost:8000/ratings" '{"rating":5,"book_id":1,"user_id":1}' "Crear rating válido (5 estrellas)"

# Intentar crear rating duplicado (debe fallar)
test_endpoint "POST" "http://localhost:8000/ratings" '{"rating":4,"book_id":1,"user_id":1}' "Intentar crear rating duplicado (debe fallar)"

# Crear más ratings para el mismo libro
test_endpoint "POST" "http://localhost:8000/ratings" '{"rating":4,"book_id":1,"user_id":2}' "Crear segundo rating para el libro 1"
test_endpoint "POST" "http://localhost:8000/ratings" '{"rating":3,"book_id":2,"user_id":1}' "Crear rating para el libro 2"

# 3. Obtener ratings y promedio
echo -e "${BLUE}3. CONSULTAS DE RATINGS${NC}"

# Obtener todos los ratings
test_endpoint "GET" "http://localhost:8000/ratings" "" "Obtener todos los ratings"

# Obtener ratings de un libro específico
test_endpoint "GET" "http://localhost:8000/ratings/book/1" "" "Obtener ratings del libro 1"

# Obtener promedio de ratings del libro 1
test_endpoint "GET" "http://localhost:8000/ratings/book/1/average" "" "Obtener promedio de ratings del libro 1"

# Obtener promedio de ratings del libro 2
test_endpoint "GET" "http://localhost:8000/ratings/book/2/average" "" "Obtener promedio de ratings del libro 2"

# 4. Pruebas de validación (deben fallar)
echo -e "${BLUE}4. PRUEBAS DE VALIDACIÓN (ERRORES ESPERADOS)${NC}"

# Intentar crear rating con libro inexistente
test_endpoint "POST" "http://localhost:8000/ratings" '{"rating":3,"book_id":999,"user_id":1}' "Rating con libro inexistente (debe fallar)"

# Intentar crear rating con usuario inexistente
test_endpoint "POST" "http://localhost:8000/ratings" '{"rating":3,"book_id":1,"user_id":999}' "Rating con usuario inexistente (debe fallar)"

# Rating fuera de rango (debe fallar en validación del servicio)
test_endpoint "POST" "http://localhost:8000/ratings" '{"rating":6,"book_id":1,"user_id":2}' "Rating fuera de rango 1-5 (debe fallar)"

# 5. Operaciones CRUD completas
echo -e "${BLUE}5. OPERACIONES CRUD COMPLETAS${NC}"

# Crear un rating para operaciones CRUD
test_endpoint "POST" "http://localhost:8000/ratings" '{"rating":4,"book_id":2,"user_id":2}' "Crear rating para operaciones CRUD"

# Obtener rating específico (último creado, asumiendo ID 4)
test_endpoint "GET" "http://localhost:8000/ratings/4" "" "Obtener rating específico"

# Actualizar rating
test_endpoint "PUT" "http://localhost:8000/ratings/4" '{"rating":5}' "Actualizar rating"

# Eliminar rating
test_endpoint "DELETE" "http://localhost:8000/ratings/4" "" "Eliminar rating"

echo -e "${GREEN}=== PRUEBAS COMPLETADAS ===${NC}"
echo -e "${YELLOW}Resumen esperado:${NC}"
echo -e "• 3 ratings creados exitosamente"
echo -e "• 1 intento de rating duplicado rechazado"
echo -e "• 2 consultas de promedio calculadas correctamente"
echo -e "• 3 validaciones de error funcionando (libro inexistente, usuario inexistente, rating fuera de rango)"
echo -e "• Operaciones CRUD completas (create, read, update, delete)"