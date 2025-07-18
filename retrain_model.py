import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score, classification_report
from imblearn.over_sampling import SMOTE
import joblib
from db_config import get_connection
from klasifikasi import CustomDecisionTree

def load_data_from_db():
    conn = get_connection()
    with conn.cursor(dictionary=True) as cursor:
        cursor.execute("""
            SELECT motorik_halus, motorik_kasar, komunikasi, membaca, pra_akademik, sosial_skill, ekspresif, menyimak, prediction, tanggal, student_id
            FROM assessment_results
        """)
        rows = cursor.fetchall()
    conn.close()

    df = pd.DataFrame(rows)
    
    # Gabungkan skor
    df['motorik_score'] = (df['motorik_halus'] + df['motorik_kasar']) / 2
    df['bahasa_score'] = (df['komunikasi'] + df['membaca'] + df['pra_akademik']) / 3
    df['kognitif_score'] = (df['sosial_skill'] + df['ekspresif'] + df['menyimak']) / 3

    # Ambil usia dari tabel students
    conn = get_connection()
    with conn.cursor(dictionary=True) as cursor:
        cursor.execute("SELECT student_id, usia FROM students")
        usia_data = cursor.fetchall()
    conn.close()

    usia_df = pd.DataFrame(usia_data)
    df = df.merge(usia_df, on='student_id', how='left')

    # Konversi label
    df['label'] = df['prediction'].str.lower().map({'terlambat': 0, 'normal': 1})
    df = df.dropna(subset=['motorik_score', 'bahasa_score', 'kognitif_score', 'usia', 'label'])

    return df[['motorik_score', 'bahasa_score', 'kognitif_score', 'usia', 'label']]

def retrain_model():
    df = load_data_from_db()
    if df.empty:
        print("❌ Tidak ada data valid untuk training.")
        return

    print(f"📊 Jumlah data: {len(df)}")

    X = df[['motorik_score', 'bahasa_score', 'kognitif_score', 'usia']].values
    y = df['label'].astype(int).values
    feature_columns = ['motorik_score', 'bahasa_score', 'kognitif_score', 'usia']

    # Scaling
    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(X)

    # SMOTE
    smote = SMOTE(random_state=42)
    X_res, y_res = smote.fit_resample(X_scaled, y)

    print(f"👉 Jumlah data training setelah SMOTE: {len(X_res)}")

    # Inisialisasi dan training model buatan sendiri
    model = CustomDecisionTree(
        max_depth=15,
        min_samples_split=5,
        min_samples_leaf=2,
        max_features='sqrt',
        ccp_alpha=0.0
    )
    model.fit(X_res, y_res, feature_names=feature_columns)

    # Prediksi & evaluasi
    y_pred = model.predict(X_res)
    accuracy = accuracy_score(y_res, y_pred)
    print(f"📊 Akurasi model (manual Decision Tree): {accuracy * 100:.2f}%")
    print("📜 Laporan Klasifikasi:")
    print(classification_report(y_res, y_pred, target_names=["Terlambat", "Normal"]))

    # Simpan model
    joblib.dump(model, 'decision_tree_model.pkl')
    joblib.dump(scaler, 'scaler.pkl')
    print("✅ Model dan scaler berhasil disimpan!")

    # Simpan log ke DB
    conn = get_connection()
    with conn.cursor() as cursor:
        cursor.execute("INSERT INTO log_model (tanggal, akurasi) VALUES (NOW(), %s)", (accuracy,))
        conn.commit()
    conn.close()
    print("🗂️ Log retraining berhasil disimpan ke database.")

if __name__ == '__main__':
    retrain_model()
