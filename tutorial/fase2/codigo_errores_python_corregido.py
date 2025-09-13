#!/usr/bin/env python3
"""
Fase 2: Código Python corregido - Versión CORREGIDA
Este código muestra las correcciones aplicadas a los errores del código original
"""

import time

def calcular_promedio(numeros):
    """
    Función que calcula el promedio de una lista de números
    CORRIGIDO: Manejo de lista vacía
    """
    if not numeros:  # CORRECCIÓN: Verificar si la lista está vacía
        print("Error: No se puede calcular el promedio de una lista vacía")
        return 0
    
    suma = sum(numeros)
    promedio = suma / len(numeros)
    return promedio

def mostrar_tabla_multiplicar(numero):
    """
    Función que muestra la tabla de multiplicar
    CORRIGIDO: Rango y validación de tipo
    """
    # CORRECCIÓN: Validar que el número sea entero
    if not isinstance(numero, int):
        print(f"Error: El valor debe ser un número entero, recibido: {type(numero).__name__}")
        return
    
    print(f"Tabla de multiplicar del {numero}:")
    
    # CORRECCIÓN: Rango de 1 a 10 (inclusive)
    for i in range(1, 11):  # Va de 1 a 10
        resultado = numero * i
        print(f"{numero} x {i} = {resultado}")

def procesar_datos():
    """
    Función principal corregida
    """
    # CORRECCIÓN: Definir la variable antes de usarla
    total_elementos = 5  # Variable definida correctamente
    print(f"Procesando {total_elementos} elementos...")
    
    # CORRECCIÓN: Usar una lista con datos en lugar de lista vacía
    lista_numeros = [10, 20, 30, 40, 50]  # Lista con valores
    promedio = calcular_promedio(lista_numeros)
    print(f"Promedio: {promedio}")
    
    # CORRECCIÓN: Pasar un entero en lugar de string
    mostrar_tabla_multiplicar(5)  # Entero en lugar de string

def main():
    """
    Función principal que ejecutará el código corregido
    """
    try:
        procesar_datos()
        mostrar_tabla_multiplicar(7)
        
        # Demostrar manejo de errores con lista vacía
        print("\n--- Probando con lista vacía ---")
        calcular_promedio([])
        
        # Demostrar manejo de errores con tipo incorrecto
        print("\n--- Probando con tipo incorrecto ---")
        mostrar_tabla_multiplicar("texto")
        
    except Exception as e:
        print(f"Error inesperado: {e}")

if __name__ == "__main__":
    main()