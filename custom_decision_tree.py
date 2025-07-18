class CustomDecisionTree:
    def __init__(self, max_depth=10, min_samples_split=5, min_samples_leaf=2, max_features='sqrt', ccp_alpha=0.01, random_state = 42):
        self.max_depth = max_depth
        self.min_samples_split = min_samples_split
        self.min_samples_leaf = min_samples_leaf
        self.max_features = max_features
        self.ccp_alpha = ccp_alpha
        self.random_state = random_state
        self.root = None
        self.feature_importances_ = None
    
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
        
        self.root = self._grow_tree(X, y, depth=0)
        
        if np.sum(self.feature_importances_) > 0:
            self.feature_importances_ /= np.sum(self.feature_importances_)
    
    def _grow_tree(self, X, y, depth):
        n_samples, n_features = X.shape
        gini = gini_impurity(y)
        class_counts = np.bincount(y, minlength=2)
        
        if (depth >= self.max_depth or 
            n_samples < self.min_samples_split or 
            n_samples < 2 * self.min_samples_leaf or 
            len(np.unique(y)) == 1 or 
            gini <= self.ccp_alpha):
            leaf_value = np.bincount(y).argmax()
            return Node(value=leaf_value, gini=gini, class_counts=class_counts)
        
        feature_indices = np.random.choice(n_features, self.max_features, replace=False)
        best_feature, best_threshold, best_gain = find_best_split(X, y, feature_indices)
        
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
                prob = node.class_counts / np.sum(node.class_counts)
            else:
                prob = np.array([0.5, 0.5])  # Default untuk kasus langka
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