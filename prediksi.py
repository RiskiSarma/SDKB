import pickle
import numpy as np
import pandas as pd

# Load model yang sudah disimpan
with open("decision_tree_model.pkl", "rb") as file:
    model = pickle.load(file)

# Periksa nama fitur yang digunakan saat model dilatih
if hasattr(model, "feature_names_in_"):
    feature_names = model.feature_names_in_
else:
    # Jika tidak ada, gunakan nama fitur yang sesuai dengan dataset pelatihan
    feature_names = ["motorik_halus", "motorik_kasar", "komunikasi", "membaca", "pemecahan_masalah", "persepsi_visual"]

def predict_student(motorik_halus, motorik_kasar, komunikasi, membaca, pemecahan_masalah, persepsi_visual):
    # Buat DataFrame input dengan nama fitur yang sesuai
    input_data = pd.DataFrame([[motorik_halus, motorik_kasar, komunikasi, membaca, pemecahan_masalah, persepsi_visual]], 
                              columns=feature_names)

    # Prediksi
    prediction = model.predict(input_data)
    return prediction[0]  # Mengembalikan hasil prediksi (Normal/Terlambat)

# Contoh Penggunaan
hasil = predict_student(5, 7, 6, 8, 5, 6)
print(f"Hasil Prediksi: {hasil}")
