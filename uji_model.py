import pandas as pd
import numpy as np
import joblib
from sklearn.metrics import (
    accuracy_score, classification_report, confusion_matrix, 
    roc_auc_score, roc_curve, precision_recall_curve, auc
)
import seaborn as sns
import matplotlib.pyplot as plt
import os
from graphviz import Digraph

# Kelas Node untuk Pohon Keputusan
class Node:
    def __init__(self, feature=None, threshold=None, left=None, right=None, value=None, gini=None, class_counts=None):
        self.feature = feature
        self.threshold = threshold
        self.left = left
        self.right = right
        self.value = value
        self.gini = gini
        self.class_counts = class_counts

# Kelas Decision Tree CART Manual
class CustomDecisionTree:
    def __init__(self, max_depth=10, min_samples_split=5, min_samples_leaf=2, max_features='sqrt', ccp_alpha=0.0, random_state=42):
        self.max_depth = max_depth
        self.min_samples_split = min_samples_split
        self.min_samples_leaf = min_samples_leaf
        self.max_features = max_features
        self.ccp_alpha = ccp_alpha
        self.random_state = random_state
        self.root = None
        self.feature_importances_ = None
        self.feature_names = None
    
    def fit(self, X, y, feature_names=None):
        n_features = X.shape[1]
        self.feature_importances_ = np.zeros(n_features)
        self.feature_names = feature_names
        
        if self.max_features == 'sqrt':
            self.max_features = int(np.sqrt(n_features))
        elif self.max_features == 'log2':
            self.max_features = int(np.log2(n_features))
        elif self.max_features is None:
            self.max_features = n_features
        else:
            self.max_features = min(self.max_features, n_features)
        
        np.random.seed(self.random_state)
        self.root = self._grow_tree(X, y, depth=0)
        
        if np.sum(self.feature_importances_) > 0:
            self.feature_importances_ /= np.sum(self.feature_importances_)
    
    def _gini_impurity(self, y):
        if len(y) == 0:
            return 0
        _, counts = np.unique(y, return_counts=True)
        probabilities = counts / len(y)
        return 1 - np.sum(probabilities ** 2)
    
    def _find_best_split(self, X, y, feature_indices):
        best_gain = -1
        best_feature = None
        best_threshold = None
        
        for feature_idx in feature_indices:
            values = np.sort(np.unique(X[:, feature_idx]))
            if len(values) < 2:
                continue
            thresholds = np.linspace(min(values), max(values), 50)
            
            for threshold in thresholds:
                left_mask = X[:, feature_idx] <= threshold
                right_mask = ~left_mask
                
                left_y = y[left_mask]
                right_y = y[right_mask]
                
                if len(left_y) < self.min_samples_leaf or len(right_y) < self.min_samples_leaf:
                    continue
                    
                gini_parent = self._gini_impurity(y)
                gini_left = self._gini_impurity(left_y)
                gini_right = self._gini_impurity(right_y)
                
                n_left = len(left_y)
                n_right = len(right_y)
                n_total = len(y)
                
                weighted_gini = (n_left / n_total) * gini_left + (n_right / n_total) * gini_right
                gini_gain = gini_parent - weighted_gini
                
                if gini_gain > best_gain:
                    best_gain = gini_gain
                    best_feature = feature_idx
                    best_threshold = threshold
                
        return best_feature, best_threshold, best_gain
    
    def _grow_tree(self, X, y, depth):
        n_samples, n_features = X.shape
        gini = self._gini_impurity(y)
        class_counts = np.bincount(y, minlength=2)
        
        if (depth >= self.max_depth or 
            n_samples < self.min_samples_split or 
            n_samples < 2 * self.min_samples_leaf or 
            len(np.unique(y)) == 1 or 
            gini <= self.ccp_alpha):
            leaf_value = np.bincount(y).argmax()
            return Node(value=leaf_value, gini=gini, class_counts=class_counts)
        
        feature_indices = np.random.choice(n_features, self.max_features, replace=False)
        best_feature, best_threshold, best_gain = self._find_best_split(X, y, feature_indices)
        
        if best_feature is None or best_gain <= 0:
            leaf_value = np.bincount(y).argmax()
            return Node(value=leaf_value, gini=gini, class_counts=class_counts)
        
        left_mask = X[:, best_feature] <= best_threshold
        right_mask = ~left_mask
        
        n_left = np.sum(left_mask)
        n_right = np.sum(right_mask)
        self.feature_importances_[best_feature] += best_gain * n_samples
        
        left_X, left_y = X[left_mask], y[left_mask]
        right_X, right_y = X[right_mask], y[right_mask]
        
        if len(left_y) < self.min_samples_leaf or len(right_y) < self.min_samples_leaf:
            leaf_value = np.bincount(y).argmax()
            return Node(value=leaf_value, gini=gini, class_counts=class_counts)
        
        left_child = self._grow_tree(left_X, left_y, depth + 1)
        right_child = self._grow_tree(right_X, right_y, depth + 1)
        
        return Node(feature=best_feature, threshold=best_threshold, left=left_child, right=right_child, gini=gini)
    
    def predict(self, X):
        return np.array([self._traverse_tree(x, self.root) for x in X])
    
    def predict_proba(self, X):
        proba = []
        for x in X:
            node = self._traverse_tree_node(x, self.root)
            if node.class_counts is not None:
                prob = (node.class_counts + 1) / (np.sum(node.class_counts) + 2)
                prob = np.clip(prob, 0.05, 0.95)
            else:
                prob = np.array([0.5, 0.5])
            proba.append(prob)
        return np.array(proba)
    
    def _traverse_tree(self, x, node):
        if node.value is not None:
            return node.value
        if x[node.feature] <= node.threshold:
            return self._traverse_tree(x, node.left)
        return self._traverse_tree(x, node.right)
    
    def _traverse_tree_node(self, x, node):
        if node.value is not None:
            return node
        if x[node.feature] <= node.threshold:
            return self._traverse_tree_node(x, node.left)
        return self._traverse_tree_node(x, node.right)
    
    def visualize_tree(self, feature_names, class_names, max_depth=None):
        dot = Digraph(comment='Decision Tree', format='png')
        dot.attr('node', shape='box')
        
        def _visualize_node(node, parent_id, branch_label, depth=0):
            node_id = str(id(node))
            if node.value is not None:
                label = f"Class: {class_names[node.value]}\nSamples: {sum(node.class_counts)}\nGini: {node.gini:.3f}"
                dot.node(node_id, label, fillcolor='lightgreen', style='filled')
            else:
                feature_name = feature_names[node.feature] if feature_names else f"Feature {node.feature}"
                label = f"{feature_name} <= {node.threshold:.3f}\nGini: {node.gini:.3f}"
                dot.node(node_id, label)
            
            if parent_id:
                dot.edge(parent_id, node_id, label=branch_label)
            
            if node.left and (max_depth is None or depth < max_depth):
                _visualize_node(node.left, node_id, "True", depth + 1)
            if node.right and (max_depth is None or depth < max_depth):
                _visualize_node(node.right, node_id, "False", depth + 1)
        
        _visualize_node(self.root, None, None)
        dot.render('decision_tree_uji', view=False, cleanup=True)
        print("[INFO] Visualisasi Decision Tree disimpan sebagai 'decision_tree_uji.png'")

# 1. Load model, scaler, dan metadata
try:
    model = joblib.load("decision_tree_model.pkl")
    scaler = joblib.load("scaler.pkl")
    metadata = joblib.load("model_metadata.pkl")
    selected_features = [
        'age', 'Motorik Kasar', 'Motorik Halus', 'Komunikasi/Bahasa Lisan',
        'Ekspresif', 'Menyimak', 'Kemampuan Pra Akademik', 'Membaca/Menulis', 'Sosial Skill'
    ]
    print("[INFO] Model, scaler, dan metadata berhasil dimuat")
    print(f"[INFO] Fitur yang digunakan: {selected_features}")
except Exception as e:
    print(f"[ERROR] Gagal memuat model, scaler, atau metadata: {e}")
    exit(1)

# 2. Load data uji
try:
    df = pd.read_csv("Test_Data.csv")
    print(f"[INFO] Jumlah data uji: {len(df)}")
    print(f"[DEBUG] Kolom data uji: {df.columns.tolist()}")
    print(f"[DEBUG] Contoh data uji:\n{df.head()}")
    print(f"[DEBUG] Nilai yang hilang:\n{df.isnull().sum()}")
except Exception as e:
    print(f"[ERROR] Gagal memuat Test_Data.csv: {e}")
    exit(1)

# Cek dan hapus NaN
if df.isnull().any().any():
    print("[WARNING] Data uji mengandung nilai NaN. Menghapus baris dengan NaN...")
    df = df.dropna()

# 3. Pisahkan fitur dan label
try:
    df['label'] = df['growth'].astype(int)
    if not all(feat in df.columns for feat in selected_features):
        raise KeyError("Beberapa fitur di selected_features tidak ditemukan di Test_Data.csv")
    X_test = df[selected_features].values
    y_test = df["label"].values
    mask = (y_test == 0) | (y_test == 1)
    X_test = X_test[mask]
    y_test = y_test[mask]
    print("[INFO] Fitur dan label berhasil dipisahkan")
    print(f"[DEBUG] Bentuk X_test: {X_test.shape}")
    print("\n[INFO] Distribusi Target (growth) di Data Uji:")
    print(pd.Series(y_test).value_counts())
    print(f"Persentase: {(pd.Series(y_test).value_counts()/len(y_test)*100).round(2)}")
except Exception as e:
    print(f"[ERROR] Gagal memisahkan fitur dan label: {e}")
    exit(1)

# 4. Standardisasi fitur uji
try:
    X_test_scaled = scaler.transform(X_test)
    print("[INFO] Fitur uji telah distandardisasi")
    print(f"[DEBUG] Bentuk X_test_scaled: {X_test_scaled.shape}")
except Exception as e:
    print(f"[ERROR] Gagal menstandarisasi fitur uji: {e}")
    exit(1)

# 5. Prediksi
try:
    y_pred = model.predict(X_test_scaled)
    y_pred_proba = model.predict_proba(X_test_scaled)[:, 1]
    print("[INFO] Prediksi berhasil dilakukan")
    print(f"[DEBUG] Bentuk y_pred: {y_pred.shape}")
except Exception as e:
    print(f"[ERROR] Gagal melakukan prediksi: {e}")
    exit(1)

# 6. Evaluasi
try:
    acc = accuracy_score(y_test, y_pred)
    roc_auc = roc_auc_score(y_test, y_pred_proba)
    print(f"[INFO] Akurasi pada Data Uji: {acc * 100:.2f}%")
    print(f"[INFO] ROC-AUC Score: {roc_auc:.4f}")
    print("[INFO] Laporan Klasifikasi:")
    print(classification_report(y_test, y_pred, target_names=["Terlambat", "Normal"], zero_division=0))
except Exception as e:
    print(f"[ERROR] Gagal mengevaluasi model: {e}")
    exit(1)

# 7. Confusion Matrix
try:
    cm = confusion_matrix(y_test, y_pred)
    cm_normalized = cm.astype('float') / cm.sum(axis=1)[:, np.newaxis]
    plt.figure(figsize=(8, 6))
    sns.heatmap(cm, annot=True, fmt='d', cmap='Blues', xticklabels=["Terlambat", "Normal"], yticklabels=["Terlambat", "Normal"])
    for i, j in np.ndindex(cm.shape):
        plt.text(j + 0.5, i + 0.5, f"\n({cm_normalized[i, j]:.2%})",
                 horizontalalignment="center", verticalalignment="center",
                 color="white" if cm[i, j] > cm.max() / 2 else "black")
    plt.xlabel('Prediksi')
    plt.ylabel('Aktual')
    plt.title('Confusion Matrix (Data Uji)')
    plt.tight_layout()
    plt.savefig("confusion_matrix_uji.png", dpi=300, bbox_inches='tight')
    print("[INFO] Confusion Matrix disimpan sebagai 'confusion_matrix_uji.png'")
except Exception as e:
    print(f"[ERROR] Gagal membuat visualisasi Confusion Matrix: {e}")

# 8. Visualisasi ROC Curve
try:
    fpr, tpr, _ = roc_curve(y_test, y_pred_proba)
    roc_auc = auc(fpr, tpr)
    plt.figure(figsize=(8, 6))
    plt.plot(fpr, tpr, label=f'ROC Curve (AUC = {roc_auc:.2f})')
    plt.plot([0, 1], [0, 1], 'k--')
    plt.xlabel('False Positive Rate')
    plt.ylabel('True Positive Rate')
    plt.title('ROC Curve (Data Uji)')
    plt.legend(loc='lower right')
    plt.tight_layout()
    plt.savefig("roc_curve_uji.png", dpi=300, bbox_inches='tight')
    print("[INFO] Grafik ROC Curve disimpan sebagai 'roc_curve_uji.png'")
except Exception as e:
    print(f"[ERROR] Gagal membuat ROC Curve: {e}")

# 9. Visualisasi Precision-Recall Curve
try:
    precisions, recalls, thresholds = precision_recall_curve(y_test, y_pred_proba)
    f1_scores = 2 * (precisions[:-1] * recalls[:-1]) / (precisions[:-1] + recalls[:-1] + 1e-10)
    optimal_idx = np.argmax(f1_scores)
    optimal_threshold = thresholds[optimal_idx]
    print(f"\n[INFO] Threshold Optimal (Data Uji): {optimal_threshold:.4f}")
    print(f"[INFO] Presisi: {precisions[optimal_idx]:.4f}")
    print(f"[INFO] Recall: {recalls[optimal_idx]:.4f}")
    print(f"[INFO] F1-score: {f1_scores[optimal_idx]:.4f}")
    
    plt.figure(figsize=(8, 6))
    plt.plot(thresholds, precisions[:-1], label='Precision')
    plt.plot(thresholds, recalls[:-1], label='Recall')
    plt.plot(thresholds, f1_scores, label='F1-Score')
    plt.axvline(x=optimal_threshold, color='r', linestyle='--', label=f'Optimal Threshold ({optimal_threshold:.4f})')
    plt.xlabel('Threshold')
    plt.ylabel('Score')
    plt.title('Precision-Recall vs Threshold (Data Uji)')
    plt.legend(loc='best')
    plt.tight_layout()
    plt.savefig("precision_recall_curve_uji.png", dpi=300, bbox_inches='tight')
    print("[INFO] Grafik Precision-Recall Curve disimpan sebagai 'precision_recall_curve_uji.png'")
except Exception as e:
    print(f"[ERROR] Gagal membuat Precision-Recall Curve: {e}")

# 10. Visualisasi Decision Tree
try:
    model.visualize_tree(feature_names=selected_features, class_names=["Terlambat", "Normal"], max_depth=3)
except Exception as e:
    print(f"[ERROR] Gagal membuat visualisasi Decision Tree: {e}")

# 11. Visualisasi Distribusi Probabilitas Prediksi
try:
    plt.figure(figsize=(10, 6))
    sns.histplot(y_pred_proba[y_test == 0], color='red', label='Terlambat', alpha=0.5, bins=30)
    sns.histplot(y_pred_proba[y_test == 1], color='green', label='Normal', alpha=0.5, bins=30)
    plt.axvline(x=optimal_threshold, color='black', linestyle='--', label=f'Threshold Optimal ({optimal_threshold:.4f})')
    plt.xlabel('Probabilitas Prediksi (Normal)')
    plt.ylabel('Frekuensi')
    plt.title('Distribusi Probabilitas Prediksi (Data Uji)')
    plt.legend()
    plt.tight_layout()
    plt.savefig("prediction_probability_distribution_uji.png", dpi=300, bbox_inches='tight')
    print("[INFO] Grafik distribusi probabilitas prediksi disimpan sebagai 'prediction_probability_distribution_uji.png'")
except Exception as e:
    print(f"[ERROR] Gagal membuat distribusi probabilitas prediksi: {e}")

# 12. Visualisasi Feature Importance
try:
    feature_importances = pd.DataFrame({
        'Fitur': selected_features,
        'Importance': model.feature_importances_
    }).sort_values('Importance', ascending=False)
    
    print("\n[INFO] Ranking Kepentingan Fitur:")
    print(feature_importances.to_string(index=False, float_format='%.4f'))
    
    plt.figure(figsize=(12, 8))
    sns.barplot(x='Importance', y='Fitur', data=feature_importances, color='skyblue')
    plt.title('Feature Importance dalam Klasifikasi (Data Uji)')
    plt.xlabel('Importance')
    plt.tight_layout()
    plt.savefig("feature_importance_uji.png", dpi=300, bbox_inches='tight')
    print("[INFO] Grafik feature importance disimpan sebagai 'feature_importance_uji.png'")
except Exception as e:
    print(f"[ERROR] Gagal membuat visualisasi Feature Importance: {e}")

plt.close('all')
print("\n[INFO] Evaluasi model pada data uji selesai! Semua file hasil telah disimpan.")