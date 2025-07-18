import pytest
import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler
from sklearn.model_selection import train_test_split
from sklearn.tree import DecisionTreeClassifier
from imblearn.over_sampling import SMOTE
from sklearn.model_selection import GridSearchCV

# Atur opsi pandas untuk menghindari FutureWarning
pd.set_option('future.no_silent_downcasting', True)

# Fungsi dari pre.py untuk diuji
def preprocess_growth(df):
    """Fungsi untuk mengonversi kolom 'growth'."""
    df["growth"] = df["growth"].astype(str).str.strip().str.lower()
    growth_mapping = {"under growth": 0, "normal": 1, "over growth": 2}
    df["growth"] = df["growth"].map(growth_mapping).fillna(-1).astype(float)
    return df

def preprocess_rating(df, rating_columns):
    """Fungsi untuk mengonversi kolom rating."""
    rating_mapping = {"excellent": 4, "good": 3, "fair": 2, "poor": 1, "invalid": 0, "nan": 0}
    for col in rating_columns:
        if col in df.columns:
            # Konversi ke string, lalu map dengan default 0 untuk nilai tidak valid
            df[col] = df[col].astype(str).str.strip().str.lower()
            df[col] = df[col].map(rating_mapping).fillna(0).astype(float)
    return df

def normalize_features(X_train, X_test):
    """Fungsi untuk normalisasi fitur."""
    scaler = StandardScaler()
    X_train_scaled = scaler.fit_transform(X_train)
    X_test_scaled = scaler.transform(X_test)
    return X_train_scaled, X_test_scaled

# Fungsi dari klasifikasi.py untuk diuji
def apply_smote(X_train_scaled, y_train):
    """Fungsi untuk menyeimbangkan data dengan SMOTE."""
    # Gunakan k_neighbors=1 untuk dataset kecil
    sampling_strategy = {1: 4, 2: 4} if len(np.unique(y_train)) > 2 else 1.0
    smote = SMOTE(random_state=42, sampling_strategy=sampling_strategy, k_neighbors=1)
    X_train_resampled, y_train_resampled = smote.fit_resample(X_train_scaled, y_train)
    return X_train_resampled, y_train_resampled

def train_model_with_grid_search(X_train, y_train):
    """Fungsi untuk melatih model dengan GridSearchCV."""
    model = DecisionTreeClassifier(random_state=42)
    param_grid = {
        'max_depth': [5, 10],
        'min_samples_split': [10, 20],
    }
    grid_search = GridSearchCV(estimator=model, param_grid=param_grid, cv=2, scoring='accuracy', n_jobs=1)
    grid_search.fit(X_train, y_train)
    return grid_search.best_estimator_, grid_search.best_params_

# Fixture untuk data dummy
@pytest.fixture
def sample_data():
    """Membuat dataset dummy untuk pengujian."""
    data = pd.DataFrame({
        "growth": ["normal", "under growth", "normal", "invalid", np.nan, "over growth"],
        "motorik_kasar": ["excellent", "good", "fair", "poor", "invalid", np.nan],
        "motorik_halus": [3, 4, 1, 2, 0, np.nan],
        "age": [5, 4, 6, 5, 8, 3]
    })
    return data

@pytest.fixture
def sample_train_test():
    """Membuat data pelatihan dan pengujian dengan kelas yang cukup untuk stratifikasi."""
    X = pd.DataFrame({
        "age": [5, 4, 6, 5, 3, 4, 5, 6, 4, 6, 5, 3],
        "motorik_kasar": [4, 3, 2, 1, 4, 3, 4, 1, 2, 3, 2, 4],
        "motorik_halus": [3, 2, 1, 2, 4, 2, 1, 3, 0, 4, 3, 1]
    })
    y = pd.Series([0, 1, 2, 1, 0, 2, 0, 1, 2, 0, 1, 2])  # Kelas 0: 4, Kelas 1: 4, Kelas 2: 4
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.3, random_state=42, stratify=y)
    return X_train, X_test, y_train, y_test

# Pengujian untuk pre.py
def test_preprocess_growth_valid(sample_data):
    """Menguji konversi kolom 'growth' untuk nilai valid."""
    df = preprocess_growth(sample_data.copy())
    expected = pd.Series([1, 0, 1, -1, -1, 2], dtype=float, name="growth")
    pd.testing.assert_series_equal(df["growth"], expected)

def test_preprocess_growth_empty():
    """Menguji konversi kolom 'growth' untuk dataset kosong."""
    df_empty = pd.DataFrame({"growth": []})
    df_result = preprocess_growth(df_empty)
    assert df_result.empty or len(df_result["growth"]) == 0, "Dataset kosong harus tetap kosong"

def test_preprocess_rating_valid(sample_data):
    """Menguji konversi kolom rating untuk nilai valid."""
    rating_columns = ["motorik_kasar"]
    df = preprocess_rating(sample_data.copy(), rating_columns)
    expected = pd.Series([4, 3, 2, 1, 0, 0], dtype=float, name="motorik_kasar")
    pd.testing.assert_series_equal(df["motorik_kasar"], expected)

def test_preprocess_rating_missing_column(sample_data):
    """Menguji konversi kolom rating untuk kolom yang hilang."""
    rating_columns = ["nonexistent_column"]
    df = preprocess_rating(sample_data.copy(), rating_columns)
    assert "nonexistent_column" not in df.columns, "Kolom yang tidak ada tidak boleh ditambahkan"

def test_normalize_features(sample_train_test):
    """Menguji normalisasi fitur dengan StandardScaler."""
    X_train, X_test, _, _ = sample_train_test
    X_train_scaled, X_test_scaled = normalize_features(X_train, X_test)
    assert np.allclose(X_train_scaled.mean(axis=0), 0, atol=1e-8), "Mean setelah normalisasi harus mendekati 0"
    assert np.allclose(X_train_scaled.std(axis=0), 1, atol=1e-8), "Standar deviasi harus mendekati 1"
    assert X_test_scaled.shape[1] == X_train.shape[1], "Jumlah kolom X_test_scaled harus sama"

def test_normalize_features_zero():
    """Menguji normalisasi untuk kolom dengan variansi nol."""
    X_train = pd.DataFrame({"col1": [1, 1, 1], "col2": [2, 3, 4]})
    X_test = pd.DataFrame({"col1": [1], "col2": [5]})
    X_train_scaled, X_test_scaled = normalize_features(X_train, X_test)
    assert not np.any(np.isnan(X_train_scaled)), "Normalisasi tidak boleh menghasilkan NaN"
    assert not np.any(np.isnan(X_test_scaled)), "Normalisasi tidak boleh menghasilkan NaN"

# Pengujian untuk klasifikasi.py
def test_apply_smote(sample_train_test):
    """Menguji penyeimbangan data dengan SMOTE untuk multi-class."""
    X_train, _, y_train, _ = sample_train_test
    scaler = StandardScaler()
    X_train_scaled = scaler.fit_transform(X_train)
    X_train_resampled, y_train_resampled = apply_smote(X_train_scaled, y_train)
    assert len(y_train_resampled) >= len(y_train), "SMOTE harus menghasilkan lebih banyak atau sama jumlah data"
    assert len(np.unique(y_train_resampled)) == len(np.unique(y_train)), "Jumlah kelas harus sama"

def test_apply_smote_unbalanced():
    """Menguji SM untuk data tidak seimbang (biner)."""
    X_train = pd.DataFrame({"feature": [1, 2, 3, 4, 5, 6, 7, 8]})
    y_train = pd.Series([0, 0, 0, 0, 0, 1, 1, 1])
    scaler = StandardScaler()
    X_train_scaled = scaler.fit_transform(X_train)
    X_train_resampled, y_train_resampled = apply_smote(X_train_scaled, y_train)
    assert sum(y_train_resampled == 1) >= sum(y_train == 1), "Kelas minoritas harus bertambah setelah SMOTE"

def test_train_model_with_grid_search(sample_train_test):
    """Menguji grid search untuk pelatihan model."""
    X_train, _, y_train, _ = sample_train_test
    best_model, best_params = train_model_with_grid_search(X_train, y_train)
    assert best_params is not None, "Grid search harus menghasilkan parameter terbaik"
    assert isinstance(best_model, DecisionTreeClassifier), "Model harus bertipe DecisionTreeClassifier"
    assert best_model.max_depth in [5, 10], "max_depth harus sesuai dengan param_grid"

def test_train_model_with_grid_search_small_data():
    """Menguji grid search untuk dataset kecil."""
    X_train = pd.DataFrame({"feature": [1, 2, 3, 4]})
    y_train = pd.Series([0, 0, 1, 1])
    best_model, best_params = train_model_with_grid_search(X_train, y_train)
    assert best_params is not None, "Grid search harus tetap berjalan pada dataset kecil"

if __name__ == "__main__":
    pytest.main(["-v"])