import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.tree import DecisionTreeClassifier
from sklearn.metrics import accuracy_score, classification_report
import joblib
import os

# 1️⃣ **Membaca Data**
file_path = "Child Growth.xlsx"  # Sesuaikan dengan lokasi file Anda
df = pd.read_excel(file_path)

# 2️⃣ **Preprocessing Data**
columns = ["age", "Motorik Kasar", "Motorik Halus", "Komunikasi/Bahasa Lisan",
            "Ekspresif","Menyimak","Kemampuan Pra Akademik", "Membaca/Menulis",
            "Sosial Skill","Growth"]

# Cek apakah semua kolom ada dalam dataframe
missing_columns = [col for col in columns if col not in df.columns]
if missing_columns:
    raise KeyError(f"❌ Kolom berikut tidak ditemukan dalam dataset: {missing_columns}")

df = df[columns]

# Mapping kategori menjadi numerik
mapping = {"poor": 0, "fair": 1, "good": 2, "excellent": 3}
pd.set_option('future.no_silent_downcasting', True)  # Hindari warning
df.replace(mapping, inplace=True)
df = df.infer_objects(copy=False)  # Menghilangkan warning

# Memfilter hanya kategori "poor" dan "fair"
df_filtered = df[df.iloc[:, 1:-1].isin([0, 1]).all(axis=1)]

if df_filtered.empty:
    raise ValueError("❌ Semua data terhapus setelah filtering. Periksa kembali nilai dalam dataset.")
df = df_filtered

# Mapping untuk growth
growth_mapping = {"under growth": 0, "growth": 1}
df["Growth"] = df["Growth"].map(growth_mapping)

# Menghapus baris dengan NaN
df.dropna(inplace=True)

# 3️⃣ **Pastikan data tidak kosong sebelum train-test split**
if df.shape[0] == 0:
    raise ValueError("❌ Dataset kosong setelah preprocessing. Periksa kembali langkah-langkah pemrosesan.")

# 4️⃣ **Membagi Data dengan Stratifikasi**
X = df.drop(columns=["Growth"])
y = df["Growth"]

# Cek apakah data cukup untuk dibagi
if df.shape[0] < 5:  # Jika data terlalu sedikit
    print("⚠️ Data terlalu sedikit untuk dibagi. Gunakan semua data untuk training.")
    X_train, X_test, y_train, y_test = X, X, y, y  # Pakai seluruh data untuk training
else:
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, stratify=y, random_state=42)

# 5️⃣ **Membangun Model Decision Tree (CART)**
model = DecisionTreeClassifier(criterion="gini", random_state=42)
model.fit(X_train, y_train)

# 6️⃣ **Validasi Model dengan Cross Validation**
cv_scores = cross_val_score(model, X_train, y_train, cv=5)
print(f"📊 Rata-rata Akurasi Cross Validation: {np.mean(cv_scores) * 100:.2f}%")

# 7️⃣ **Evaluasi Model**
y_pred = model.predict(X_test)
accuracy = accuracy_score(y_test, y_pred)

print(f"📊 Akurasi Model: {accuracy * 100:.2f}%")
print("\n📜 Laporan Klasifikasi:\n", classification_report(y_test, y_pred))

# 8️⃣ **Menyimpan Model**
model_path = "decision_tree_model.pkl"
joblib.dump(model, model_path)
print(f"✅ Model berhasil disimpan di: {os.path.abspath(model_path)}")
