import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
import json
import joblib

from sklearn.model_selection import train_test_split, GridSearchCV, cross_val_score
from sklearn.preprocessing import StandardScaler
from sklearn.tree import DecisionTreeClassifier, plot_tree
from sklearn.metrics import (
    accuracy_score, roc_auc_score, classification_report,
    confusion_matrix, roc_curve, precision_recall_curve
)
from imblearn.over_sampling import SMOTE

# 1️⃣ Load Data
print("🔄 Memuat data untuk sistem deteksi dini...")
data = pd.read_csv("Converted_Child_Growth.csv")
print(f"✅ Data berhasil dimuat: {data.shape[0]} sampel, {data.shape[1]} fitur")

# 2️⃣ Tangani Missing Values
print("\n🔍 Memeriksa dan menangani missing values...")
if data.isnull().sum().sum() > 0:
    missing_info = data.isnull().sum()
    print(f"⚠️  Ditemukan missing values pada kolom: {missing_info[missing_info > 0].to_dict()}")
    data = data.fillna(data.median())
    print("✅ Missing values telah ditangani menggunakan median imputation")
else:
    print("✅ Tidak ada missing values ditemukan")

# 3️⃣ Eksplorasi Awal
print("\n📊 Analisis distribusi kelas untuk deteksi dini:")
print("Target distribution (growth status):")
growth_counts = data["growth"].value_counts()
growth_percentages = (growth_counts / len(data) * 100).round(2)
for status, count in growth_counts.items():
    status_name = "Terlambat Tumbuh" if status == 0 else "Normal" if status == 1 else "Kelebihan Tumbuh"
    print(f"  {status_name} (kode {status}): {count} sampel ({growth_percentages[status]}%)")

# 4️⃣ Pisahkan Fitur dan Target
print("\n🎯 Mempersiapkan data untuk deteksi dini...")
X = data.drop(columns=["growth"])
y = data["growth"]

# Filter untuk deteksi dini: fokus pada 'normal' (0) vs 'terlambat' (1)
# Sesuai preprocessing: 0 = normal, 1 = terlambat (perlu deteksi dini)
mask = (y == 0) | (y == 1)
X = X[mask]
y = y[mask]
y_binary = y.copy()  # Tidak perlu remapping: 0 = normal, 1 = terlambat

print(f"✅ Data disiapkan untuk deteksi dini:")
print(f"  - Kelas 1 (Terlambat - Perlu Deteksi Dini): {sum(y_binary == 1)} sampel")
print(f"  - Kelas 0 (Normal): {sum(y_binary == 0)} sampel")

# 5️⃣ Split Train-Test
print("\n🔄 Membagi data untuk pelatihan dan pengujian...")
X_train, X_test, y_train, y_test = train_test_split(
    X, y_binary, test_size=0.2, stratify=y_binary, random_state=42
)

print(f"📊 Distribusi data setelah pembagian:")
print(f"  Data pelatihan: {X_train.shape[0]} sampel ({X_train.shape[0]/len(X)*100:.1f}%)")
print(f"  Data pengujian: {X_test.shape[0]} sampel ({X_test.shape[0]/len(X)*100:.1f}%)")
print(f"  Total fitur: {X_train.shape[1]}")

print(f"\n📊 Distribusi target untuk deteksi dini:")
train_dist = pd.Series(y_train).value_counts()
test_dist = pd.Series(y_test).value_counts()
print(f"  Training - Terlambat: {train_dist.get(1, 0)}, Normal: {train_dist.get(0, 0)}")
print(f"  Testing - Terlambat: {test_dist.get(1, 0)}, Normal: {test_dist.get(0, 0)}")

# 6️⃣ Standardisasi
print("\n🔄 Melakukan standardisasi fitur...")
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)
print("✅ Standardisasi selesai")

# 7️⃣ SMOTE untuk Menangani Ketidakseimbangan Data
print("\n⚖️  Menangani ketidakseimbangan data dengan SMOTE...")
print(f"  Sebelum SMOTE: {len(y_train)} sampel")
smote = SMOTE(random_state=42)
X_train_resampled, y_train_resampled = smote.fit_resample(X_train_scaled, y_train)
print(f"  Setelah SMOTE: {len(y_train_resampled)} sampel")

resampled_dist = pd.Series(y_train_resampled).value_counts()
resampled_pct = (resampled_dist / len(y_train_resampled) * 100).round(1)
print(f"  Distribusi setelah SMOTE:")
print(f"    Terlambat (perlu deteksi dini): {resampled_dist.get(1, 0)} ({resampled_pct.get(1, 0)}%)")
print(f"    Normal: {resampled_dist.get(0, 0)} ({resampled_pct.get(0, 0)}%)")

# 8️⃣ GridSearchCV untuk Optimasi Decision Tree CART
print("\n🔍 Mencari parameter optimal untuk Decision Tree CART...")
param_grid = {
    'max_depth': [5, 10, 15, 20],
    'min_samples_split': [2, 5, 10],
    'min_samples_leaf': [1, 2, 4],
    'max_features': ['sqrt', 'log2', None],
    'ccp_alpha': [0.0, 0.001, 0.01],
    'criterion': ['gini', 'entropy']
}

grid_search = GridSearchCV(
    DecisionTreeClassifier(random_state=42),
    param_grid,
    cv=5,
    scoring='roc_auc',
    n_jobs=-1,
    verbose=1
)

grid_search.fit(X_train_resampled, y_train_resampled)
best_model = grid_search.best_estimator_

print("✅ Model deteksi dini terbaik telah dilatih")
print(f"  Parameter terbaik: {grid_search.best_params_}")
print(f"  ROC-AUC Score: {grid_search.best_score_:.4f}")

# 9️⃣ Evaluasi Model pada Data Training
print("\n📊 Evaluasi model deteksi dini pada data training...")
y_train_pred = best_model.predict(X_train_resampled)
y_train_proba = best_model.predict_proba(X_train_resampled)[:, 1]
train_accuracy = accuracy_score(y_train_resampled, y_train_pred)
roc_auc = roc_auc_score(y_train_resampled, y_train_proba)
cv_scores = cross_val_score(best_model, X_train_resampled, y_train_resampled, cv=5, scoring='accuracy')

print(f"  Akurasi Training: {train_accuracy * 100:.2f}%")
print(f"  ROC-AUC Score: {roc_auc:.4f}")
print(f"  Cross Validation Accuracy: {np.mean(cv_scores) * 100:.2f}% ± {np.std(cv_scores) * 100:.2f}%")

# 🔟 Menentukan ROC & Threshold Optimal untuk Deteksi Dini
print("\n🎯 Menentukan threshold optimal untuk deteksi dini...")
fpr, tpr, thresholds = roc_curve(y_train_resampled, y_train_proba)
# Untuk deteksi dini, kita ingin sensitivitas tinggi (recall tinggi)
f1_scores = 2 * (tpr * (1 - fpr)) / (tpr + (1 - fpr) + 1e-6)
optimal_idx = np.argmax(f1_scores)
optimal_threshold = thresholds[optimal_idx]
print(f"  Threshold optimal untuk deteksi dini: {optimal_threshold:.4f}")
print(f"  Sensitivitas pada threshold ini: {tpr[optimal_idx]:.4f}")
print(f"  Spesifisitas pada threshold ini: {1-fpr[optimal_idx]:.4f}")

# 1️⃣1️⃣ Evaluasi pada Data Test
print("\n📊 Evaluasi sistem deteksi dini pada data test...")
y_test_pred = (best_model.predict_proba(X_test_scaled)[:, 1] >= optimal_threshold).astype(int)
y_test_proba = best_model.predict_proba(X_test_scaled)[:, 1]

test_accuracy = accuracy_score(y_test, y_test_pred)
test_roc_auc = roc_auc_score(y_test, y_test_proba)

print(f"  Akurasi Deteksi pada Data Test: {test_accuracy * 100:.2f}%")
print(f"  ROC-AUC Score pada Data Test: {test_roc_auc:.4f}")

# Classification Report untuk Deteksi Dini
print("\n📄 Laporan Klasifikasi Sistem Deteksi Dini:")
print(classification_report(y_test, y_test_pred, target_names=["Normal", "Terlambat"]))

# Confusion Matrix untuk Deteksi Dini
cm = confusion_matrix(y_test, y_test_pred)
print("\n📊 Confusion Matrix Sistem Deteksi Dini:")
print("     Prediksi")
print("Aktual   Normal  Deteksi")
print(f"Normal     {cm[0,0]:3d}     {cm[0,1]:3d}")
print(f"Deteksi    {cm[1,0]:3d}     {cm[1,1]:3d}")

# Interpretasi untuk deteksi dini
tn, fp, fn, tp = cm.ravel()
sensitivity = tp / (tp + fn)  # Recall untuk kelas positif
specificity = tn / (tn + fp)  # Recall untuk kelas negatif
precision = tp / (tp + fp)    # Precision untuk kelas positif

print(f"\n📊 Metrik Deteksi Dini:")
print(f"  Sensitivitas (Recall): {sensitivity:.4f} - Kemampuan mendeteksi anak yang perlu intervensi")
print(f"  Spesifisitas: {specificity:.4f} - Kemampuan mengidentifikasi anak normal")
print(f"  Precision: {precision:.4f} - Ketepatan deteksi dini")

# Visualisasi Confusion Matrix
plt.figure(figsize=(8, 6))
sns.heatmap(cm, annot=True, fmt='d', cmap='Blues', 
            xticklabels=["Normal", "Terlambat"], 
            yticklabels=["Normal", "Terlambat"])
plt.title("Confusion Matrix - Sistem Deteksi Dini Tumbuh Kembang Anak")
plt.ylabel("Kelas Aktual")
plt.xlabel("Kelas Terdeteksi")
plt.tight_layout()
plt.savefig("confusion_matrix_deteksi_dini.png", dpi=300)
plt.close()
print("✅ Confusion matrix disimpan: confusion_matrix_deteksi_dini.png")

# Precision-Recall Curve
precisions, recalls, thresholds_pr = precision_recall_curve(y_test, y_test_proba)
f1_scores_test = 2 * (precisions[:-1] * recalls[:-1]) / (precisions[:-1] + recalls[:-1] + 1e-6)
plt.figure(figsize=(10, 6))
plt.plot(thresholds_pr, precisions[:-1], 'b--', label='Precision', linewidth=2)
plt.plot(thresholds_pr, recalls[:-1], 'g-', label='Recall (Sensitivitas)', linewidth=2)
plt.plot(thresholds_pr, f1_scores_test, 'r-.', label='F1 Score', linewidth=2)
plt.axvline(x=optimal_threshold, color='purple', linestyle=':', linewidth=2, 
            label=f'Threshold Optimal = {optimal_threshold:.4f}')
plt.xlabel('Threshold')
plt.ylabel('Score')
plt.title('Kurva Precision-Recall untuk Deteksi Dini')
plt.legend()
plt.grid(True, alpha=0.3)
plt.tight_layout()
plt.savefig("precision_recall_deteksi_dini.png", dpi=300)
plt.close()
print("✅ Precision-Recall curve disimpan: precision_recall_deteksi_dini.png")

# 1️⃣2️⃣ Visualisasi Decision Tree
plt.figure(figsize=(24, 12))
plot_tree(
    best_model,
    filled=True,
    feature_names=X.columns,
    class_names=["Normal", "Terlambat"],
    rounded=True,
    proportion=True,
    max_depth=4,
    fontsize=12,
    impurity=True
)
plt.title("Visualisasi Decision Tree CART - Sistem Deteksi Dini Tumbuh Kembang Anak", fontsize=16)
plt.tight_layout()
plt.savefig("decision_tree_deteksi_dini.png", dpi=300, bbox_inches='tight')
plt.close()
print("✅ Visualisasi decision tree disimpan: decision_tree_deteksi_dini.png")

# 1️⃣3️⃣ Feature Importance untuk Deteksi Dini
importances = pd.Series(best_model.feature_importances_, index=X.columns)
top_features = importances.sort_values(ascending=False).head(10)

plt.figure(figsize=(12, 8))
top_features.sort_values().plot(kind='barh', color='skyblue', edgecolor='navy')
plt.title("Top 10 Fitur Penting untuk Deteksi Dini Tumbuh Kembang", fontsize=14)
plt.xlabel("Tingkat Kepentingan")
plt.tight_layout()
plt.savefig("feature_importance_deteksi_dini.png", dpi=300)
plt.close()
print("✅ Feature importance disimpan: feature_importance_deteksi_dini.png")

print(f"\n🎯 Top 5 Fitur Penting untuk Deteksi Dini:")
for i, (feature, importance) in enumerate(top_features.head(5).items(), 1):
    print(f"  {i}. {feature}: {importance:.4f}")

# 1️⃣4️⃣ Simpan Model & Metadata
print("\n💾 Menyimpan model dan metadata...")
joblib.dump(best_model, "model_deteksi_dini_cart.pkl")
joblib.dump(scaler, "scaler_deteksi_dini.pkl")

metadata = {
    'system_type': 'Early Detection System',
    'target_condition': 'Deteksi Dini Terlambat Tumbuh',
    'model': 'DecisionTreeClassifier (CART)',
    'scaler': 'StandardScaler',
    'optimal_threshold': float(optimal_threshold),
    'detection_metrics': {
        'sensitivity': float(sensitivity),
        'specificity': float(specificity),
        'precision': float(precision),
        'f1_score': float(2 * precision * sensitivity / (precision + sensitivity))
    },
    'performance_metrics': {
        'train_accuracy': float(train_accuracy),
        'test_accuracy': float(test_accuracy),
        'cv_mean': float(np.mean(cv_scores)),
        'cv_std': float(np.std(cv_scores)),
        'train_roc_auc': float(roc_auc),
        'test_roc_auc': float(test_roc_auc)
    },
    'feature_info': {
        'feature_names': X.columns.tolist(),
        'feature_importances': dict(zip(X.columns, best_model.feature_importances_)),
        'top_5_features': dict(top_features.head(5)),
        'n_features': X.shape[1]
    },
    'model_params': best_model.get_params(),
    'class_mapping': {
        0: "Normal",
        1: "Terlambat"
    },
    'training_info': {
        'original_samples': int(len(data)),
        'processed_samples': int(len(X)),
        'training_samples': int(len(X_train)),
        'test_samples': int(len(X_test)),
        'resampled_samples': int(len(y_train_resampled)),
        'smote_applied': True
    },
    'interpretation': {
        'purpose': 'Sistem deteksi dini untuk mengidentifikasi anak yang berisiko mengalami keterlambatan tumbuh kembang',
        'threshold_rationale': f'Threshold {optimal_threshold:.4f} dipilih untuk memaksimalkan deteksi dini dengan menjaga keseimbangan sensitivitas dan spesifisitas',
        'clinical_relevance': 'Model ini dapat membantu tenaga kesehatan dalam mengidentifikasi anak yang membutuhkan intervensi dini'
    }
}

with open("metadata_deteksi_dini.json", "w") as f:
    json.dump(metadata, f, indent=4)
print("✅ Model dan metadata disimpan:")
print("  - model_deteksi_dini_cart.pkl")
print("  - scaler_deteksi_dini.pkl") 
print("  - metadata_deteksi_dini.json")

# 1️⃣5️⃣ Visualisasi ROC-AUC Curve
plt.figure(figsize=(10, 8))
plt.plot(fpr, tpr, label=f"ROC Curve (AUC = {roc_auc:.4f})", color='darkorange', linewidth=2)
plt.plot([0, 1], [0, 1], linestyle="--", color='gray', alpha=0.7)
plt.scatter(fpr[optimal_idx], tpr[optimal_idx], color='red', s=100, zorder=5,
            label=f'Threshold Optimal ({optimal_threshold:.4f})')
plt.xlabel("False Positive Rate (1 - Spesifisitas)")
plt.ylabel("True Positive Rate (Sensitivitas)")
plt.title("ROC-AUC Curve - Sistem Deteksi Dini Tumbuh Kembang Anak")
plt.legend(loc="lower right")
plt.grid(True, alpha=0.3)
plt.tight_layout()
plt.savefig("roc_auc_deteksi_dini.png", dpi=300)
plt.close()
print("✅ ROC-AUC curve disimpan: roc_auc_deteksi_dini.png")

# 1️⃣6️⃣ Visualisasi Cross Validation Accuracy
plt.figure(figsize=(10, 6))
plt.plot(range(1, 6), cv_scores, marker='o', linestyle='-', color='green', linewidth=2, markersize=8)
plt.axhline(y=np.mean(cv_scores), color='red', linestyle='--', linewidth=2,
            label=f'Rata-rata: {np.mean(cv_scores):.4f}')
plt.fill_between(range(1, 6), cv_scores, alpha=0.3, color='green')
plt.title("Cross Validation Accuracy - Sistem Deteksi Dini (5-Fold CV)")
plt.xlabel("Fold ke-")
plt.ylabel("Akurasi")
plt.xticks(range(1, 6))
plt.ylim(0, 1)
plt.legend()
plt.grid(True, alpha=0.3)
plt.tight_layout()
plt.savefig("cv_accuracy_deteksi_dini.png", dpi=300)
plt.close()
print("✅ Cross Validation Accuracy disimpan: cv_accuracy_deteksi_dini.png")

# 1️⃣7️⃣ Ringkasan Hasil
print("\n" + "="*80)
print("🎯 RINGKASAN SISTEM DETEKSI DINI TUMBUH KEMBANG ANAK")
print("="*80)
print(f"📊 Akurasi Deteksi: {test_accuracy * 100:.2f}%")
print(f"📊 ROC-AUC Score: {test_roc_auc:.4f}")
print(f"📊 Sensitivitas (Deteksi Kasus Positif): {sensitivity:.4f}")
print(f"📊 Spesifisitas (Identifikasi Normal): {specificity:.4f}")
print(f"📊 Precision: {precision:.4f}")
print(f"📊 Threshold Optimal: {optimal_threshold:.4f}")
print(f"\n🔍 Fitur Paling Penting:")
for i, (feature, importance) in enumerate(top_features.head(3).items(), 1):
    print(f"  {i}. {feature}: {importance:.4f}")
print(f"\n📈 Model berhasil dilatih dengan {len(y_train_resampled)} sampel training")
print(f"📈 Model diuji dengan {len(y_test)} sampel testing")
print("\n✅ Sistem deteksi dini siap digunakan untuk identifikasi anak yang berisiko!")
print("="*80)