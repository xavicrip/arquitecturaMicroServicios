#!/bin/bash

# Script simple de prueba para Reviews API
echo "=== PRUEBA RÁPIDA DE REVIEWS API ==="
echo ""

# Verificar servicios
echo "Verificando servicios..."
curl -s http://localhost:8000 > /dev/null && echo "✓ Gateway OK" || echo "✗ Gateway FAIL"
curl -s http://localhost:8001 > /dev/null && echo "✓ Authors OK" || echo "✗ Authors FAIL"  
curl -s http://localhost:8002 > /dev/null && echo "✓ Books OK" || echo "✗ Books FAIL"
curl -s http://localhost:8003 > /dev/null && echo "✓ Reviews OK" || echo "✗ Reviews FAIL"
echo ""

# Probar creación de review
echo "Creando review de prueba..."
response=$(curl -s -X POST http://localhost:8000/reviews \
  -H "Content-Type: application/json" \
  -d '{"comment":"Excelente libro","rating":5,"book_id":1}')

if echo "$response" | grep -q "data"; then
    echo "✓ Review creada exitosamente"
else
    echo "✗ Error al crear review: $response"
fi

echo ""
echo "Listando reviews..."
curl -s http://localhost:8000/reviews

echo ""
echo "=== PRUEBA COMPLETADA ==="
