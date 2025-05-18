import pandas as pd
import numpy as np
from sklearn.model_selection import GridSearchCV, cross_val_score
from sklearn.tree import DecisionTreeClassifier
from sklearn.metrics import accuracy_score, classification_report, confusion_matrix, roc_auc_score, precision_recall_curve
from imblearn.over_sampling import SMOTE
from sklearn.tree import plot_tree
from sklearn.preprocessing import StandardScaler
import matplotlib.pyplot as plt
import joblib
import os

# 1️⃣ Kumpulkan data siswa
data = pd.read_csv("Train_Data.csv")
print("✅ Data siswa berhasil dikumpulkan!")
print(f"📊 Jumlah data: {data.shape[0]} siswa dengan {data.shape[1]} kolom")

# 2️⃣ Ekstrak indikator perkembangan
# Konversi skor kualitatif menjadi numerik (jika belum)
skor_map = {"excellent": 4, "good": 3, "fair": 2, "poor": 1}

# Daftar indikator berdasarkan domain
indikator_kognitif = ["Kemampuan Pra Akademik", "Membaca/Menulis", "Sosial Skill"]
indikator_bahasa = ["Komunikasi/Bahasa Lisan", "Ekspresif", "Menyimak"]
indikator_motorik = ["Motorik Kasar", "Motorik Halus"]

print("✅ Indikator perkembangan berhasil diekstrak:")
print(f"👉 Kognitif: {', '.join(indikator_kognitif)}")
print(f"👉 Bahasa: {', '.join(indikator_bahasa)}")
print(f"👉 Motorik: {', '.join(indikator_motorik)}")

# Pastikan semua kolom indikator dalam format numerik
# Konversi kolom string ke numerik jika diperlukan
semua_indikator = indikator_kognitif + indikator_bahasa + indikator_motorik
for indikator in semua_indikator:
    if indikator in data.columns:
        # Cek apakah kolom berisi string
        if data[indikator].dtype == 'object':
            # Jika ya, konversi ke numerik dengan mapping
            data[indikator] = data[indikator].map(skor_map)
            print(f"✅ Mengkonversi kolom '{indikator}' ke numerik")

# Periksa missing values dan tangani
if data.isnull().sum().sum() > 0:
    print(f"⚠️ Terdapat {data.isnull().sum().sum()} nilai yang hilang.")
    # Menampilkan kolom dengan nilai yang hilang
    print(data.isnull().sum()[data.isnull().sum() > 0])
    # Menangani missing values
    data = data.fillna(data.median())
    print("✅ Missing values ditangani dengan nilai median.")

# 3️⃣ Data Exploration - Periksa distribusi target
print("\n📊 Distribusi Target (growth):")
target_counts = data["growth"].value_counts()
print(target_counts)
print(f"Persentase: {(target_counts/len(data)*100).round(2)}")

# Memisahkan fitur dan target
# Simpan daftar nama kolom yang digunakan sebagai fitur
feature_columns = data.drop(columns=["growth"]).columns.tolist()
X = data.drop(columns=["growth"])
y = data["growth"]

# Konversi target menjadi biner (Terlambat: 0, Normal: 1)
y_binary = y.map({0: 0, 1: 1, 2: 1})  # Menggabungkan normal dan over growth
print(f"✅ Data dipilah berdasarkan domain, dengan {X.shape[1]} indikator")

# Distribusi target setelah konversi
print("\n📊 Distribusi Target Setelah Konversi ke Biner:")
target_binary_counts = y_binary.value_counts()
print(target_binary_counts)
print(f"Persentase: {(target_binary_counts/len(y_binary)*100).round(2)}")

# PERUBAHAN: Gunakan seluruh data training untuk training (tidak ada split)
X_train = X
y_train = y_binary

# Standardisasi fitur numerik
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)

# Konversi kembali ke DataFrame untuk mempertahankan nama kolom
X_train_scaled = pd.DataFrame(X_train_scaled, columns=X_train.columns)

# 4️⃣ Seimbangkan data dengan SMOTE - menggunakan subsample untuk mengatasi masalah memori
# MODIFIKASI: Gunakan sampling_strategy yang lebih kecil untuk mengurangi jumlah data
smote = SMOTE(random_state=42, sampling_strategy=0.6)  # Kurangi dari 0.8 ke 0.6
X_train_resampled, y_train_resampled = smote.fit_resample(X_train_scaled, y_train)
print(f"✅ Data telah diseimbangkan menggunakan SMOTE")
print(f"👉 Jumlah data training sebelum SMOTE: {len(y_train)}")
print(f"👉 Jumlah data training setelah SMOTE: {len(y_train_resampled)}")
print(f"👉 Distribusi kelas setelah SMOTE: {pd.Series(y_train_resampled).value_counts()}")

# 5️⃣ Terapkan metode Decision Tree CART dengan parameter yang lebih sederhana
model = DecisionTreeClassifier(random_state=42)

# MODIFIKASI: Sederhanakan grid parameter untuk mengurangi kombinasi dan penggunaan memori
param_grid = {
    'max_depth': [5, 10, None],  # Kurangi jumlah opsi
    'min_samples_split': [10, 20],  # Kurangi jumlah opsi
    'min_samples_leaf': [5, 10],  # Kurangi jumlah opsi
    'max_features': ['sqrt', None],  # Kurangi jumlah opsi
    'ccp_alpha': [0.0, 0.01]  # Kurangi jumlah opsi
}

print("🔍 Mencari parameter optimal untuk model Decision Tree CART...")
# MODIFIKASI: Tambahkan n_jobs=-1 untuk paralelisasi dan kurangi cv untuk mengurangi memori
grid_search = GridSearchCV(
    estimator=model, 
    param_grid=param_grid, 
    cv=3,  # Kurangi dari 5 ke 3
    scoring='roc_auc',
    n_jobs=-1,  # Paralelisasi
    verbose=1
)

# MODIFIKASI: Handle memory error dengan membatasi ukuran data jika diperlukan
try:
    grid_search.fit(X_train_resampled, y_train_resampled)
except MemoryError:
    # Jika terjadi MemoryError, kurangi ukuran sampel
    print("⚠️ Terjadi Memory Error! Mengurangi ukuran dataset...")
    
    # Ambil sampel acak 50% dari data
    sample_indices = np.random.choice(
        len(X_train_resampled), 
        size=int(len(X_train_resampled) * 0.5), 
        replace=False
    )
    X_sample = X_train_resampled.iloc[sample_indices]
    y_sample = y_train_resampled.iloc[sample_indices]
    
    # Jalankan grid search pada sampel yang lebih kecil
    grid_search.fit(X_sample, y_sample)
    print("✅ Grid search dijalankan pada sampel data yang lebih kecil")
else:
    print("✅ Grid search berhasil dijalankan pada semua data")

best_model = grid_search.best_estimator_
print(f"✅ Model Decision Tree CART terbaik telah dilatih")
print(f"👉 Parameter terbaik: {grid_search.best_params_}")
print(f"👉 Skor validasi terbaik: {grid_search.best_score_:.4f}")

# 6️⃣ Evaluasi model dengan cross-validation - menggunakan CV yang lebih kecil
cv_scores = cross_val_score(best_model, X_train_resampled, y_train_resampled, cv=3, scoring='accuracy')
print(f"📊 Rata-rata Akurasi Cross Validation: {np.mean(cv_scores) * 100:.2f}%")
print(f"👉 Skor individu: {[f'{score * 100:.2f}%' for score in cv_scores]}")

# Evaluasi pada data latih
y_train_pred = best_model.predict(X_train_resampled)
train_accuracy = accuracy_score(y_train_resampled, y_train_pred)
print(f"📊 Akurasi Model pada Data Latih: {train_accuracy * 100:.2f}%")

# Tampilkan laporan klasifikasi pada data latih
print("\n📜 Laporan Klasifikasi (Data Latih):")
train_class_report = classification_report(y_train_resampled, y_train_pred, target_names=["Terlambat", "Normal"], zero_division=1)
print(train_class_report)

# Generate and display confusion matrix
print("\n📊 Confusion Matrix (Data Latih):")
train_cm = confusion_matrix(y_train_resampled, y_train_pred)
print(train_cm)

# Calculate and display normalized confusion matrix
train_cm_normalized = train_cm.astype('float') / train_cm.sum(axis=1)[:, np.newaxis]
print("\n📊 Normalized Confusion Matrix (Data Latih):")
print(np.round(train_cm_normalized, 2))

# Visualize confusion matrix
plt.figure(figsize=(10, 8))
classes = ["Terlambat", "Normal"]
plt.imshow(train_cm, interpolation='nearest', cmap=plt.cm.Blues)
plt.title('Confusion Matrix - Decision Tree CART')
plt.colorbar()
tick_marks = np.arange(len(classes))
plt.xticks(tick_marks, classes, rotation=45)
plt.yticks(tick_marks, classes)

# Add text annotations in the confusion matrix
thresh = train_cm.max() / 2.0
for i, j in np.ndindex(train_cm.shape):
    plt.text(j, i, f"{train_cm[i, j]}\n({train_cm_normalized[i, j]:.2%})",
             horizontalalignment="center",
             color="white" if train_cm[i, j] > thresh else "black")

plt.tight_layout()
plt.ylabel('True label')
plt.xlabel('Predicted label')
plt.savefig("confusion_matrix.png")
print("✅ Visualisasi Confusion Matrix telah disimpan sebagai 'confusion_matrix.png'")

# 7️⃣ Menentukan threshold optimal menggunakan precision-recall curve pada data latih
y_train_proba = best_model.predict_proba(X_train_resampled)[:, 1]
precisions, recalls, thresholds = precision_recall_curve(y_train_resampled, y_train_proba)

# Hitung F1 score untuk setiap threshold
f1_scores = 2 * (precisions[:-1] * recalls[:-1]) / (precisions[:-1] + recalls[:-1] + 1e-10)
optimal_idx = np.argmax(f1_scores)
optimal_threshold = thresholds[optimal_idx]

print(f"\n📊 Threshold Optimal: {optimal_threshold:.4f}")
print(f"👉 Presisi pada threshold optimal: {precisions[optimal_idx]:.4f}")
print(f"👉 Recall pada threshold optimal: {recalls[optimal_idx]:.4f}")
print(f"👉 F1-score pada threshold optimal: {f1_scores[optimal_idx]:.4f}")

# Visualisasi distribusi threshold
plt.figure(figsize=(12, 8))
plt.plot(thresholds, precisions[:-1], 'b--', label='Precision')
plt.plot(thresholds, recalls[:-1], 'g-', label='Recall')
plt.plot(thresholds, f1_scores, 'r-.', label='F1 Score')
plt.axvline(x=optimal_threshold, color='purple', linestyle=':', label=f'Optimal Threshold = {optimal_threshold:.4f}')
plt.xlabel('Threshold')
plt.ylabel('Score')
plt.title('Precision, Recall dan F1 Score berdasarkan Threshold')
plt.legend()
plt.grid(True)
plt.savefig("threshold_evaluation.png")
print("✅ Visualisasi Threshold telah disimpan sebagai 'threshold_evaluation.png'")

# 8️⃣ Visualisasikan pohon keputusan
plt.figure(figsize=(20, 10))
plot_tree(
    best_model,  # Model Decision Tree yang sudah di-training
    feature_names=X.columns,  # Nama fitur
    class_names=["Terlambat", "Normal"],  # Nama kelas sesuai dengan urutan 0, 1
    filled=True,  # Warna node berdasarkan kelas
    rounded=True,  # Bentuk node bulat
    proportion=True,  # Tampilkan proporsi sampel di setiap node
    max_depth=3,  # Batasi kedalaman pohon yang ditampilkan
    fontsize=10  # Ukuran font
)
plt.title("Visualisasi Decision Tree Klasifikasi Keterlambatan Belajar", fontsize=16)
plt.savefig("visualisasi_decision_tree.png")
print("✅ Visualisasi Decision Tree telah disimpan sebagai 'visualisasi_decision_tree.png'")

# 9️⃣ Tampilkan hasil pelatihan model
print("\n📑 HASIL PELATIHAN MODEL KLASIFIKASI KETERLAMBATAN BELAJAR 📑")
print("=" * 50)
print(f"Total Data Training yang Digunakan: {len(y_train_resampled)}")

# Hitungan jumlah data per kategori
jumlah_normal = np.sum(y_train_resampled == 1)
jumlah_terlambat = np.sum(y_train_resampled == 0)

print(f"Jumlah Data Kategori Normal: {jumlah_normal} ({jumlah_normal/len(y_train_resampled)*100:.1f}%)")
print(f"Jumlah Data Kategori Terlambat: {jumlah_terlambat} ({jumlah_terlambat/len(y_train_resampled)*100:.1f}%)")
print("=" * 50)

# Skor ketepatan klasifikasi pada data latih
print(f"Akurasi Klasifikasi (Data Latih): {train_accuracy * 100:.2f}%")
print(f"Cross-Validation Score: {np.mean(cv_scores) * 100:.2f}%")
print("=" * 50)

# Indikator yang paling berpengaruh
feature_importances = pd.DataFrame({
    'Indikator': X.columns,
    'Importance': best_model.feature_importances_
}).sort_values('Importance', ascending=False)

print("TOP 5 INDIKATOR PALING BERPENGARUH:")
print(feature_importances.head(5).to_string(index=False))
print("=" * 50)

# Menyimpan model
# Menyimpan model
model_path = "decision_tree_cart_model.pkl"
joblib.dump(best_model, model_path)
print(f"✅ Model berhasil disimpan di: {os.path.abspath(model_path)}")

# Simpan juga threshold optimal untuk digunakan saat prediksi
threshold_data = {
    'optimal_threshold': optimal_threshold,
    'precision': precisions[optimal_idx],
    'recall': recalls[optimal_idx],
    'f1_score': f1_scores[optimal_idx],
    'feature_columns': feature_columns,  # Simpan nama kolom fitur juga
    'scaler': scaler,  # Simpan scaler untuk preprocessing data baru
    # Tambahkan informasi penting lainnya yang diperlukan untuk prediksi
    'feature_importances': dict(zip(X.columns, best_model.feature_importances_)),
    'model_params': best_model.get_params(),
    'class_names': ["Terlambat", "Normal"]
}
threshold_path = "optimal_threshold.pkl"
joblib.dump(threshold_data, threshold_path)
print(f"✅ Threshold optimal berhasil disimpan di: {os.path.abspath(threshold_path)}")

# Tambahkan verifikasi bahwa file berhasil disimpan dengan benar
try:
    # Verifikasi file tersimpan
    loaded_threshold = joblib.load(threshold_path)
    print(f"✅ Verifikasi threshold berhasil: {list(loaded_threshold.keys())}")
    print(f"   Nilai threshold optimal: {loaded_threshold['optimal_threshold']:.4f}")
    if 'feature_columns' in loaded_threshold and 'scaler' in loaded_threshold:
        print(f"   Jumlah fitur tersimpan: {len(loaded_threshold['feature_columns'])}")
        print("   Scaler tersimpan dengan benar")
    else:
        print("⚠️ Beberapa data penting tidak tersimpan dengan benar!")
except Exception as e:
    print(f"⚠️ Gagal verifikasi file threshold: {e}")