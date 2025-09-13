#!/usr/bin/env python3
"""
Fase 1: Contador regresivo en Python
Tutorial de programación - Comparación Python vs C++
"""

import time  # Importa la librería time para medir tiempo y pausas

def contador_regresivo(numero_inicial):
    """
    Función que implementa un contador regresivo desde un número dado hasta 0
    
    Args:
        numero_inicial (int): El número desde donde empezar la cuenta regresiva
    """
    print(f"Iniciando contador regresivo desde {numero_inicial}")
    
    # Ciclo while que continúa mientras el número sea mayor a 0
    while numero_inicial > 0:
        print(f"Cuenta regresiva: {numero_inicial}")  # Muestra el número actual
        numero_inicial -= 1  # Decrementa el contador en 1
        time.sleep(0.1)  # Pausa de 0.1 segundos para visualizar mejor el conteo
    
    print("¡Cuenta regresiva terminada! 🚀")

def main():
    """
    Función principal que ejecuta el programa y mide el tiempo de ejecución
    """
    # Registra el tiempo de inicio
    tiempo_inicio = time.time()
    
    # Ejecuta el contador regresivo desde 10
    contador_regresivo(10)
    
    # Registra el tiempo de finalización
    tiempo_fin = time.time()
    
    # Calcula y muestra el tiempo total de ejecución
    tiempo_total = tiempo_fin - tiempo_inicio
    print(f"\nTiempo total de ejecución: {tiempo_total:.4f} segundos")

# Verifica si este archivo se está ejecutando directamente
if __name__ == "__main__":
    main()