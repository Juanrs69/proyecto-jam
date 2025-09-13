#!/usr/bin/env python3
"""
Fase 2: Código Python con errores - Versión ORIGINAL (con errores)
Este código contiene errores intencionados para demostrar la detección y corrección
"""

import time

def calcular_promedio(numeros):
    """
    Función que calcula el promedio de una lista de números
    ERRORES INCLUIDOS INTENCIONALMENTE
    """
    # ERROR 1: División por cero no manejada
    suma = sum(numeros)
    promedio = suma / len(numeros)  # Falla si la lista está vacía
    return promedio

def mostrar_tabla_multiplicar(numero):
    """
    Función que muestra la tabla de multiplicar
    ERRORES INCLUIDOS INTENCIONALMENTE  
    """
    print(f"Tabla de multiplicar del {numero}:")
    
    # ERROR 2: Rango incorrecto (debería ser 1 a 10, no 0 a 9)
    for i in range(10):  # Va de 0 a 9, no de 1 a 10
        resultado = numero * i
        print(f"{numero} x {i} = {resultado}")

def procesar_datos():
    """
    Función principal con múltiples errores
    """
    # ERROR 3: Variable no definida
    print(f"Procesando {total_elementos} elementos...")  # total_elementos no existe
    
    # ERROR 4: Lista vacía causará error en calcular_promedio
    lista_vacia = []
    promedio = calcular_promedio(lista_vacia)
    print(f"Promedio: {promedio}")
    
    # ERROR 5: Tipo de dato incorrecto
    mostrar_tabla_multiplicar("5")  # String en lugar de int

def main():
    """
    Función principal que ejecutará el código con errores
    """
    try:
        procesar_datos()
        mostrar_tabla_multiplicar(7)
    except Exception as e:
        print(f"Error encontrado: {e}")

if __name__ == "__main__":
    main()