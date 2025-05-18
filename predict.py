import sys
import json
import pickle
import numpy as np
import os
from sklearn.tree import DecisionTreeClassifier

def get_recommendations(motorik_score, bahasa_score, kognitif_score, usia, prediction, threshold):
    """
    Memberikan rekomendasi berdasarkan skor yang rendah dan usia
    
    Args:
        motorik_score (float): Skor motorik anak
        bahasa_score (float): Skor bahasa anak
        kognitif_score (float): Skor kognitif anak
        usia (int): Usia anak dalam tahun
        prediction (str): Hasil prediksi dari model ("Normal" atau "Terlambat")
        threshold (float): Nilai batas minimum untuk menentukan skor rendah
        
    Return:
        dict: Rekomendasi untuk setiap area yang perlu peningkatan
    """
    recommendations = {}
    
    # Hanya berikan rekomendasi jika prediksi adalah "Terlambat"
    if prediction != "Terlambat":
        return recommendations
    
    # Rekomendasi untuk motorik (disesuaikan dengan usia)
    if motorik_score < threshold:
        if usia < 5:
            recommendations["motorik"] = [
                "Latihan menggambar lingkaran dan garis lurus",
                "Lakukan aktivitas fisik ringan seperti bermain bola kecil",
                "Permainan menyusun balok sederhana"
            ]
        else:
            recommendations["motorik"] = [
                "Lakukan aktivitas fisik yang menyenangkan seperti bermain bola atau berenang",
                "Latih keseimbangan dengan permainan sederhana seperti berjalan di garis lurus",
                "Berikan mainan yang membutuhkan keterampilan motorik halus seperti puzzle atau balok susun",
                "Ajak anak melakukan kegiatan menggambar dan mewarnai secara rutin"
            ]
    
    # Rekomendasi untuk bahasa (disesuaikan dengan usia)
    if bahasa_score < threshold:
        if usia < 5:
            recommendations["bahasa"] = [
                "Bacakan cerita pendek setiap hari",
                "Ajak bernyanyi lagu anak-anak",
                "Ajak bicara dengan kalimat pendek dan jelas"
            ]
        else:
            recommendations["bahasa"] = [
                "Bacakan buku cerita setiap hari dan diskusikan isinya",
                "Ajak anak berbicara dan dengarkan dengan penuh perhatian",
                "Perkenalkan kosakata baru melalui permainan kata",
                "Gunakan lagu dan musik untuk mengembangkan kemampuan bahasa"
            ]
    
    # Rekomendasi untuk kognitif (disesuaikan dengan usia)
    if kognitif_score < threshold:
        if usia < 5:
            recommendations["kognitif"] = [
                "Permainan mengelompokkan warna",
                "Mengenal angka 1-10",
                "Ajak bermain puzzle sederhana (4-6 keping)"
            ]
        else:
            recommendations["kognitif"] = [
                "Berikan permainan yang melibatkan pemecahan masalah sederhana",
                "Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran",
                "Ajak anak bermain peran untuk mengembangkan imajinasi",
                "Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"
            ]
    
    # Rekomendasi Umum (tetap sama untuk semua usia)
    if len(recommendations) > 1:
        recommendations["umum"] = [
            "Konsultasikan dengan dokger anak atau psikolog anak untuk evaluasi lebih lanjut",
            "Pertimbangkan untuk mengikuti program stimulasi terpadu",
            "Pastikan asupan gizi anak terpenuhi dengan baik",
            "Berikan waktu istirahat dan tidur yang cukup"
        ]
    
    return recommendations

def predict(data):
    """
    Memprediksi status perkembangan anak menggunakan model Decision Tree yang telah dilatih
    
    Args:
        data (dict): Dictionary berisi skor motorik, bahasa, kognitif, dan usia
        
    Return:
        dict: Hasil prediksi dan rekomendasi dalam format JSON
    """
    try:
        # Get the directory where this script is located
        script_dir = os.path.dirname(os.path.abspath(__file__))
        
        # Full path to the model file
        model_path = os.path.join(script_dir, 'decision_tree_cart_model.pkl')
        
        # Full path to the optimal threshold file
        threshold_path = os.path.join(script_dir, 'optimal_threshold.pkl')
        
        # Check if model file exists
        if not os.path.exists(model_path):
            return {
                "status": "error",
                "message": f"Model file not found at {model_path}"
            }
        
        # Load model yang sudah ditraining sebelumnya
        with open(model_path, 'rb') as f:
            model_data = pickle.load(f)
        
        # Print debug information about the model
        print(f"Debug - Model type: {type(model_data)}")
        
        # Load optimal threshold dari pickle file dengan handling yang lebih fleksibel
        if not os.path.exists(threshold_path):
            return {
                "status": "error",
                "message": f"Threshold file not found at {threshold_path}"
            }
            
        try:
            with open(threshold_path, 'rb') as f:
                threshold_data = pickle.load(f)
                print(f"Debug - Raw threshold data type: {type(threshold_data)}")
                
                # Fungsi untuk ekstrak nilai threshold dari berbagai format data
                def extract_threshold(data):
                    # Case 1: Data adalah angka langsung
                    if isinstance(data, (int, float)):
                        print(f"Debug - Found direct number: {data}")
                        return data
                    
                    # Case 2: Data adalah dictionary dengan key 'threshold'
                    if isinstance(data, dict) and 'threshold' in data:
                        print(f"Debug - Found threshold in dict: {data['threshold']}")
                        return data['threshold']
                    
                    # Case 3: Data adalah list dan elemen pertama adalah angka
                    if isinstance(data, (list, np.ndarray)) and len(data) > 0:
                        if isinstance(data[0], (int, float)):
                            print(f"Debug - Found number in list: {data[0]}")
                            return data[0]
                        # Case 4: Data adalah list dari list dengan elemen pertama angka
                        if isinstance(data[0], (list, np.ndarray)) and len(data[0]) > 0 and isinstance(data[0][0], (int, float)):
                            print(f"Debug - Found number in nested list: {data[0][0]}")
                            return data[0][0]
                    
                    # Case 5: Data adalah dictionary dengan key kemungkinan lain
                    if isinstance(data, dict):
                        for key in ['optimal', 'optimal_value', 'value', 'threshold_value', 'best', 'score']:
                            if key in data and isinstance(data[key], (int, float)):
                                print(f"Debug - Found number in dict with key {key}: {data[key]}")
                                return data[key]
                        
                        # Coba key pertama yang nilainya angka
                        for key, value in data.items():
                            if isinstance(value, (int, float)):
                                print(f"Debug - Found numeric value for key {key}: {value}")
                                return value
                    
                    # Jika tidak bisa menentukan threshold, gunakan default dengan warning
                    print("WARNING: Couldn't extract threshold value - defaulting to 3.0")
                    return 3.0
                
                # Ekstrak threshold dari data
                optimal_threshold = extract_threshold(threshold_data)
                print(f"Debug - Final extracted optimal threshold: {optimal_threshold}")
                
        except Exception as e:
            print(f"Debug - Error loading threshold: {str(e)}")
            print("Debug - Using default threshold of 3.0")
            optimal_threshold = 3.0
        
        # Ekstrak fitur dari data input
        motorik_score = float(data['motorik_score'])
        bahasa_score = float(data['bahasa_score'])
        kognitif_score = float(data['kognitif_score'])
        usia = int(data['usia'])
        
        # Handle different possible model formats
        if isinstance(model_data, np.ndarray):
            # If the model is an array, we need to use manual prediction logic
            print("Debug - Model is a numpy array, using manual prediction")
            
            # Buat array fitur untuk prediksi
            features = np.array([motorik_score, bahasa_score, kognitif_score, usia])
            
            # Implementasi prediksi menggunakan optimal_threshold yang telah dimuat
            avg_score = (motorik_score + bahasa_score + kognitif_score) / 3
            prediction = "Terlambat" if avg_score < optimal_threshold else "Normal"
            
            print(f"Debug - Manual prediction using optimal threshold {optimal_threshold}: {prediction}")
        else:
            # Jika model adalah objek scikit-learn, gunakan metode .predict()
            # Buat array fitur untuk prediksi 
            # PENTING: Urutan fitur ini harus sesuai dengan urutan yang digunakan saat melatih model
            features = np.array([[motorik_score, bahasa_score, kognitif_score, usia]])
            
            # Print debug information
            print(f"Debug - Input features: {features}")
            
            # Lakukan prediksi menggunakan model
            prediction_result = model_data.predict(features)[0]
            
            # Konversi hasil prediksi numerik (jika ada) ke kategori
            # Asumsi: Model mungkin menghasilkan 0=Normal, 1=Terlambat, atau langsung string
            if isinstance(prediction_result, (int, np.integer)):
                prediction = "Terlambat" if prediction_result == 1 else "Normal"
            else:
                prediction = prediction_result
                
            print(f"Debug - Model prediction: {prediction}")
            
        # Dapatkan rekomendasi berdasarkan hasil prediksi, skor, dan usia
        # Gunakan optimal_threshold yang sama untuk rekomendasi
        recommendations = get_recommendations(
            motorik_score, 
            bahasa_score, 
            kognitif_score, 
            usia, 
            prediction,
            optimal_threshold  # Gunakan optimal_threshold yang sama di sini
        )
            
        # Return hasil prediksi dalam format JSON
        return {
            "prediction": prediction,
            "recommendations": recommendations,
            "debug_info": f"Usia: {usia}, Model prediction: {prediction}, Threshold: {optimal_threshold}"
        }
    
    except Exception as e:
        import traceback
        error_details = traceback.format_exc()
        return {
            "status": "error",
            "message": f"{str(e)}\nTraceback: {error_details}"
        }

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python predict.py [json_file_path]", file=sys.stderr)
        sys.exit(1)
    
    # Baca file JSON yang berisi data
    file_path = sys.argv[1]
    with open(file_path, 'r') as f:
        data = json.load(f)
    
    # Print input data for debugging
    print(f"Debug - Input data: {json.dumps(data)}")
    
    # Lakukan prediksi
    prediction_result = predict(data)
    
    # Output prediksi (akan ditangkap oleh PHP)
    print(json.dumps(prediction_result))