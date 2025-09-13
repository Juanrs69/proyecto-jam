#include <iostream>  // Para operaciones de entrada y salida (cout, cin)
#include <chrono>    // Para medir tiempo de ejecución
#include <thread>    // Para pausas en la ejecución (sleep)

using namespace std;  // Permite usar cout, cin, etc. sin std::

/**
 * Función que implementa un contador regresivo desde un número dado hasta 0
 * 
 * @param numero_inicial El número desde donde empezar la cuenta regresiva
 */
void contadorRegresivo(int numero_inicial) {
    cout << "Iniciando contador regresivo desde " << numero_inicial << endl;
    
    // Ciclo while que continúa mientras el número sea mayor a 0
    while (numero_inicial > 0) {
        cout << "Cuenta regresiva: " << numero_inicial << endl;  // Muestra el número actual
        numero_inicial--;  // Decrementa el contador en 1
        this_thread::sleep_for(chrono::milliseconds(100));  // Pausa de 100ms (0.1 segundos)
    }
    
    cout << "¡Cuenta regresiva terminada! 🚀" << endl;
}

/**
 * Función principal que ejecuta el programa y mide el tiempo de ejecución
 */
int main() {
    // Registra el tiempo de inicio usando chrono de alta resolución
    auto tiempo_inicio = chrono::high_resolution_clock::now();
    
    // Ejecuta el contador regresivo desde 10
    contadorRegresivo(10);
    
    // Registra el tiempo de finalización
    auto tiempo_fin = chrono::high_resolution_clock::now();
    
    // Calcula la duración total en milisegundos
    auto duracion = chrono::duration_cast<chrono::milliseconds>(tiempo_fin - tiempo_inicio);
    
    // Muestra el tiempo total de ejecución
    cout << endl << "Tiempo total de ejecución: " << duracion.count() << " milisegundos" << endl;
    cout << "Equivalente a: " << duracion.count() / 1000.0 << " segundos" << endl;
    
    return 0;  // Indica que el programa terminó exitosamente
}