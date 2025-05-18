import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from sklearn.model_selection import train_test_split, GridSearchCV, cross_val_score
from sklearn.tree import DecisionTreeClassifier, plot_tree
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
from sklearn.preprocessing import StandardScaler

# Atur opsi pandas untuk menghilangkan FutureWarning (opsional)
pd.set_option('future.no_silent_downcasting', True)

# Load dataset
file_path = "Child Growth.xlsx"
df = pd.read_excel(file_path)

# Menampilkan beberapa data awal sebelum mapping
print("📌 Data Sebelum Konversi:")
print(df.head())

# Cek apakah kolom 'growth' ada
if 'growth' not in df.columns:
    print("🚨 ERROR: Kolom 'growth' tidak ditemukan dalam dataset!")
    exit()

# Membersihkan data growth dari spasi yang tidak terlihat
df["growth"] = df["growth"].astype(str).str.strip().str.lower()  # Convert ke lowercase

# Tambahan mapping termasuk over growth
growth_mapping = {
    "under growth": 0,
    "normal": 1,
    "over growth": 2
}

# Konversi growth ke angka
df["growth"] = df["growth"].map(growth_mapping)

# Mengisi NaN dengan nilai default (-1 menandakan data tidak valid)
df["growth"] = df["growth"].fillna(-1)

# Hanya menggunakan kolom yang diperlukan
selected_columns = [
    "age", "Motorik Kasar", "Motorik Halus", "Komunikasi/Bahasa Lisan",
    "Ekspresif", "Menyimak", "Kemampuan Pra Akademik",
    "Membaca/Menulis", "Sosial Skill", "growth"
]

# Pilih hanya kolom yang ada di dataset
available_columns = [col for col in selected_columns if col in df.columns]
df_selected = df[available_columns].copy()  # Gunakan .copy() untuk menghindari SettingWithCopyWarning

# Kolom yang perlu dikonversi (semua kolom kecuali age dan growth)
rating_columns = [
    "Motorik Kasar", "Motorik Halus", "Komunikasi/Bahasa Lisan",
    "Ekspresif", "Menyimak", "Kemampuan Pra Akademik",
    "Membaca/Menulis", "Sosial Skill"
]

rating_mapping = {
    "excellent": 4, "good": 3, "fair": 2, "poor": 1
}

# Konversi rating dengan pengecekan menggunakan .loc
for col in rating_columns:
    if col in df_selected.columns:
        df_selected.loc[:, col] = df_selected[col].astype(str).str.strip().str.lower().map(rating_mapping)
        df_selected.loc[:, col] = df_selected[col].fillna(0).infer_objects(copy=False)  # Gunakan infer_objects

# Menampilkan data setelah konversi
print("\n📌 Data Setelah Konversi:")
print(df_selected.head())

# Hapus data dengan growth yang tidak valid (bukan 0, 1, atau 2)
df_selected = df_selected[df_selected["growth"].isin([0, 1, 2])]

# Menyiapkan fitur dan target
X = df_selected.drop(columns=["growth"])
y = df_selected["growth"]

# Split data
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42, stratify=y)

# ====================================================
# ✨ NORMALISASI FITUR MENGGUNAKAN STANDARDSCALER
# ====================================================
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)

# Ubah kembali ke DataFrame dengan nama kolom asli
X_train_scaled_df = pd.DataFrame(X_train_scaled, columns=X.columns)
X_test_scaled_df = pd.DataFrame(X_test_scaled, columns=X.columns)

# Tambahkan kembali kolom target
train_scaled = pd.concat([X_train_scaled_df, y_train.reset_index(drop=True)], axis=1)
test_scaled = pd.concat([X_test_scaled_df, y_test.reset_index(drop=True)], axis=1)

# ====================================================
# ✨ PENYIMPANAN DATASET
# ====================================================
# 1. Dataset yang sudah dikonversi
converted_file_path = "Converted_Child_Growth.csv"
df_selected.to_csv(converted_file_path, index=False)
print(f"\n💾 Dataset yang sudah dikonversi telah disimpan ke: {converted_file_path}")

# 2. Data latih (tanpa normalisasi)
train_file_path = "Train_Data.csv"
pd.concat([X_train, y_train], axis=1).to_csv(train_file_path, index=False)
print(f"💾 Data latih (training) telah disimpan ke: {train_file_path}")

# 3. Data uji (tanpa normalisasi)
test_file_path = "Test_Data.csv"
pd.concat([X_test, y_test], axis=1).to_csv(test_file_path, index=False)
print(f"💾 Data uji (testing) telah disimpan ke: {test_file_path}")

# 4. Data latih hasil normalisasi
train_scaled_path = "Train_Data_Normalized.csv"
train_scaled.to_csv(train_scaled_path, index=False)
print(f"💾 Data latih (normalized) telah disimpan ke: {train_scaled_path}")

# 5. Data uji hasil normalisasi
test_scaled_path = "Test_Data_Normalized.csv"
test_scaled.to_csv(test_scaled_path, index=False)
print(f"💾 Data uji (normalized) telah disimpan ke: {test_scaled_path}")
