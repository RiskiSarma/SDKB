import sys
import json
import os
import numpy as np
from sklearn.tree import DecisionTreeClassifier
import joblib

def get_recommendations(motorik_score, bahasa_score, kognitif_score, usia, detection_result, threshold):
    """
    Memberikan rekomendasi berdasarkan skor rendah dan usia untuk intervensi dini.
    """
    recommendations = {}
    
    if detection_result == "Normal" and min(motorik_score, bahasa_score, kognitif_score) >= threshold:
        return recommendations
    
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
                "Latih keseimbangan dengan permainan seperti berjalan di garis lurus",
                "Berikan mainan yang membutuhkan keterampilan motorik halus seperti puzzle atau balok susun",
                "Ajak anak menggambar dan mewarnai secara rutin"
            ]
    
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
    
    if kognitif_score < threshold:
        if usia < 5:
            recommendations["kognitif"] = [
                "Permainan mengelompokkan warna",
                "Mengenal angka 1-10",
                "Ajak bermain puzzle sederhana (4-6 keping)"
            ]
        else:
            recommendations["kognitif"] = [
                "Berikan permainan yang melatih pemecahan masalah sederhana",
                "Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran",
                "Ajak anak bermain peran untuk mengembangkan imajinasi",
                "Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"
            ]
    
    if len(recommendations) > 1 or min(motorik_score, bahasa_score, kognitif_score) < 50:
        recommendations["umum"] = [
            "Konsultasikan dengan dokter anak atau psikolog anak untuk evaluasi lebih lanjut",
            "Pertimbangkan untuk mengikuti program stimulasi terpadu",
            "Pastikan asupan gizi anak terpenuhi dengan baik",
            "Berikan waktu istirahat dan tidur yang cukup"
        ]
    elif len(recommendations) > 0:
        recommendations["umum"] = [
            "Lakukan stimulasi yang konsisten setiap hari",
            "Pantau perkembangan anak secara berkala",
            "Berikan dukungan dan motivasi positif"
        ]
    
    return recommendations

def evaluate_critical_scores(motorik_score, bahasa_score, kognitif_score, usia):
    """
    Mengevaluasi skor yang sangat rendah yang memerlukan perhatian segera.
    """
    critical_threshold = 50
    concern_threshold = 60
    
    critical_areas = []
    concern_areas = []
    
    areas = {
        "motorik": motorik_score,
        "bahasa": bahasa_score,
        "kognitif": kognitif_score
    }
    
    for area, score in areas.items():
        if score < critical_threshold:
            critical_areas.append(area)
        elif score < concern_threshold:
            concern_areas.append(area)
    
    if len(critical_areas) >= 2:
        concern_level = "Tinggi"
    elif len(critical_areas) == 1:
        concern_level = "Sedang-Tinggi"
    elif len(concern_areas) >= 2:
        concern_level = "Sedang"
    elif len(concern_areas) == 1:
        concern_level = "Rendah-Sedang"
    else:
        concern_level = "Rendah"
    
    return {
        "critical_areas": critical_areas,
        "concern_areas": concern_areas,
        "concern_level": concern_level,
        "needs_immediate_attention": len(critical_areas) > 0
    }

def detect(data, debug=False):
    """
    Mendeteksi status pertumbuhan anak berdasarkan aturan sederhana.
    Status "Normal" jika semua skor ≥ 60, "Terlambat" jika ada skor < 60.
    Model hanya digunakan untuk analisis tambahan tanpa mengubah hasil utama.
    """
    try:
        script_dir = os.path.dirname(os.path.abspath(__file__))
        
        model_path = os.path.join(script_dir, 'model_cart_detection.pkl')
        scaler_path = os.path.join(script_dir, 'scaler.pkl')
        metadata_path = os.path.join(script_dir, 'model_metadata.json')
        
        for path, name in [(model_path, "Model"), (scaler_path, "Scaler"), (metadata_path, "Metadata")]:
            if not os.path.exists(path):
                return {
                    "status": "error",
                    "message": f"{name} file tidak ditemukan di {path}"
                }
        
        try:
            model = joblib.load(model_path)
            scaler = joblib.load(scaler_path)
            with open(metadata_path, 'r') as f:
                metadata = json.load(f)
        except Exception as e:
            return {
                "status": "error",
                "message": f"Error memuat file model: {str(e)}"
            }
        
        optimal_threshold = metadata.get('optimal_threshold', 0.6)  # Default 0.6 (60%)
        
        motorik_score = float(data['motorik_score'])
        bahasa_score = float(data['bahasa_score'])
        kognitif_score = float(data['kognitif_score'])
        usia = int(data['usia'])
        
        # Scaling features (9 fitur sesuai scaler)
        motorik_score_scaled = motorik_score / 20
        bahasa_score_scaled = bahasa_score / 20
        kognitif_score_scaled = kognitif_score / 20
        
        features = np.array([
            [usia, kognitif_score_scaled, bahasa_score_scaled, kognitif_score_scaled,
             bahasa_score_scaled, kognitif_score_scaled, kognitif_score_scaled,
             motorik_score_scaled, motorik_score_scaled]
        ])
        
        features_scaled = scaler.transform(features)
        
        # Evaluasi kritis
        critical_evaluation = evaluate_critical_scores(motorik_score, bahasa_score, kognitif_score, usia)
        
        # Logika deteksi utama: "Normal" jika semua skor ≥ 60
        delayed_areas = []
        if motorik_score < 60:
            delayed_areas.append("motorik")
        if bahasa_score < 60:
            delayed_areas.append("bahasa")
        if kognitif_score < 60:
            delayed_areas.append("kognitif")
        
        final_detection = "Normal" if not delayed_areas else f"Terlambat pada: {', '.join(delayed_areas)}"
        
        # Model hanya untuk analisis, tidak mengubah hasil utama
        detection_proba = model.predict_proba(features_scaled)[0]
        detection_score = detection_proba[1]
        ml_detection = "Normal" if detection_score >= optimal_threshold else "Terlambat"
        
        recommendations = get_recommendations(
            motorik_score,
            bahasa_score,
            kognitif_score,
            usia,
            final_detection,
            60
        )
        
        result = {
            "prediction": final_detection,
            "recommendations": recommendations
        }
        
        if debug:
            result["analysis"] = {
                "ml_detection": ml_detection,
                "ml_confidence": float(detection_score),
                "critical_override": False,
                "detection_method": "Rule-Based",
                "critical_evaluation": critical_evaluation
            }
            result["debug_info"] = {
                "usia": usia,
                "detection_score": float(detection_score),
                "optimal_threshold": float(optimal_threshold),
                "features_raw": features.tolist(),
                "features_scaled": features_scaled.tolist()
            }
        
        return result
    
    except Exception as e:
        import traceback
        error_details = traceback.format_exc()
        return {
            "status": "error",
            "message": f"{str(e)}\nTraceback: {error_details}"
        }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Penggunaan: python child_growth_detection.py [json_file_path] [--debug]", file=sys.stderr)
        sys.exit(1)
    
    file_path = sys.argv[1]
    debug = "--debug" in sys.argv
    
    with open(file_path, 'r') as f:
        data = json.load(f)
    
    detection_result = detect(data, debug=debug)
    
    print(json.dumps(detection_result))