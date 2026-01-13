#!/bin/bash

# Script para iniciar el servicio de Ratings
# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== INICIANDO SERVICIO DE RATINGS ===${NC}"

# Verificar que composer esté disponible
if ! command -v ../composer &> /dev/null; then
    echo -e "${RED}ERROR: Composer no encontrado en el directorio raíz${NC}"
    exit 1
fi

# Cambiar al directorio del servicio
cd LumenRatingsApi

echo -e "${YELLOW}Instalando dependencias...${NC}"
../composer install --no-interaction --optimize-autoloader

echo -e "${YELLOW}Ejecutando migraciones...${NC}"
php artisan migrate --force

echo -e "${GREEN}✓ Servicio de Ratings configurado correctamente${NC}"
echo -e "${BLUE}Iniciando servidor en puerto 8007...${NC}"
echo -e "${YELLOW}URL del servicio: http://localhost:8007${NC}"
echo -e "${YELLOW}Presiona Ctrl+C para detener${NC}"
echo ""

# Iniciar el servidor
php -S localhost:8007 -t public