import pandas as pd
import numpy as np
import joblib
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from imblearn.under_sampling import RandomUnderSampler
import os

# Atur opsi pandas untuk menghilangkan FutureWarning
pd.set_option('future.no_silent_downcasting', True)

# Load dataset
file_path = "Child Growth.xlsx"
try:
    df = pd.read_excel(file_path)
except FileNotFoundError:
    print("🚨 ERROR: File Child_Growth.xlsx tidak ditemukan!")
    exit()

# Menampilkan beberapa data awal sebelum mapping
print("📌 Data Sebelum Konversi:")
print(df.head())

# Cek apakah kolom 'growth' ada
if 'growth' not in df.columns:
    print("🚨 ERROR: Kolom 'growth' tidak ditemukan dalam dataset!")
    exit()

# Membersihkan data growth
df["growth"] = df["growth"].astype(str).str.strip().str.lower()

# Mapping untuk under growth dan normal
growth_mapping = {
    "under growth": 1,
    "normal": 0
}

# Konversi growth ke angka
df["growth"] = df["growth"].map(growth_mapping)

# Hanya menggunakan kolom yang diperlukan
selected_columns = [
    "age", "Motorik Kasar", "Motorik Halus", "Komunikasi/Bahasa Lisan",
    "Ekspresif", "Menyimak", "Kemampuan Pra Akademik",
    "Membaca/Menulis", "Sosial Skill", "growth"
]

# Pilih hanya kolom yang ada di dataset
available_columns = [col for col in selected_columns if col in df.columns]
df_selected = df[available_columns].copy()

# Kolom yang perlu dikonversi
rating_columns = [
    "Motorik Kasar", "Motorik Halus", "Komunikasi/Bahasa Lisan",
    "Ekspresif", "Menyimak", "Kemampuan Pra Akademik",
    "Membaca/Menulis", "Sosial Skill"
]

rating_mapping = {
    "excellent": 4, "good": 3, "fair": 2, "poor": 1
}

# Konversi rating
for col in rating_columns:
    if col in df_selected.columns:
        df_selected.loc[:, col] = df_selected[col].astype(str).str.strip().str.lower().map(rating_mapping)
        df_selected.loc[:, col] = df_selected[col].fillna(0).infer_objects(copy=False)

# Menampilkan data setelah konversi
print("\n📌 Data Setelah Konversi:")
print(df_selected.head())

# Hapus baris dengan NaN atau growth tidak valid
df_selected = df_selected.dropna()
df_selected = df_selected[df_selected["growth"].isin([0, 1])]

# Cek nilai NaN setelah pembersihan
print("\n[DEBUG] Nilai yang hilang setelah pembersihan:")
print(df_selected.isnull().sum())

# Cek distribusi awal
print("\n📌 Distribusi Awal Target (growth):")
print(df_selected["growth"].value_counts())
print(f"Persentase: {(df_selected['growth'].value_counts() / len(df_selected) * 100).round(2)}")

# Menyiapkan fitur dan target
X = df_selected.drop(columns=["growth"])
y = df_selected["growth"].astype(int)

# Menyeimbangkan kelas menggunakan undersampling
rus = RandomUnderSampler(sampling_strategy='auto', random_state=42)
X_balanced, y_balanced = rus.fit_resample(X, y)

# Cek distribusi setelah balancing
print("\n📌 Distribusi Setelah Balancing (Sebelum Split):")
print(pd.Series(y_balanced).value_counts())
print(f"Persentase: {(pd.Series(y_balanced).value_counts() / len(y_balanced) * 100).round(2)}")

# Split data
X_train, X_test, y_train, y_test = train_test_split(
    X_balanced, y_balanced, test_size=0.2, random_state=42, stratify=y_balanced
)

# Cek distribusi setelah split
print("\n📌 Distribusi Data Latih (y_train):")
print(pd.Series(y_train).value_counts())
print(f"Persentase: {(pd.Series(y_train).value_counts() / len(y_train) * 100).round(2)}")

print("\n📌 Distribusi Data Uji (y_test):")
print(pd.Series(y_test).value_counts())
print(f"Persentase: {(pd.Series(y_test).value_counts() / len(y_test) * 100).round(2)}")

# Normalisasi fitur
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)

# Ubah kembali ke DataFrame
X_train_scaled_df = pd.DataFrame(X_train_scaled, columns=X.columns)
X_test_scaled_df = pd.DataFrame(X_test_scaled, columns=X.columns)

# Tambahkan kembali kolom target
train_data = pd.concat([X_train.reset_index(drop=True), y_train.reset_index(drop=True)], axis=1)
test_data = pd.concat([X_test.reset_index(drop=True), y_test.reset_index(drop=True)], axis=1)
train_scaled = pd.concat([X_train_scaled_df, y_train.reset_index(drop=True)], axis=1)
test_scaled = pd.concat([X_test_scaled_df, y_test.reset_index(drop=True)], axis=1)

# Penyimpanan dataset
converted_file_path = "Converted_Child_Growth.csv"
df_selected.to_csv(converted_file_path, index=False)
print(f"\n💾 Dataset yang sudah dikonversi telah disimpan ke: {os.path.abspath(converted_file_path)}")

train_file_path = "Train_Data.csv"
train_data.to_csv(train_file_path, index=False)
print(f"💾 Data latih (training) telah disimpan ke: {os.path.abspath(train_file_path)}")

test_file_path = "Test_Data.csv"
test_data.to_csv(test_file_path, index=False)
print(f"💾 Data uji (testing) telah disimpan ke: {os.path.abspath(test_file_path)}")

train_scaled_path = "Train_Data_Normalized.csv"
train_scaled.to_csv(train_scaled_path, index=False)
print(f"💾 Data latih (normalized) telah disimpan ke: {os.path.abspath(train_scaled_path)}")

test_scaled_path = "Test_Data_Normalized.csv"
test_scaled.to_csv(test_scaled_path, index=False)
print(f"💾 Data uji (normalized) telah disimpan ke: {os.path.abspath(test_scaled_path)}")

# Simpan scaler
scaler_path = "scaler_pre.pkl"
joblib.dump(scaler, scaler_path)
print(f"💾 Scaler disimpan di: {os.path.abspath(scaler_path)}")