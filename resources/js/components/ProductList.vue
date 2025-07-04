<template>
  <div class="container">
    <h1 class="title">Каталог товаров</h1>

    <!-- Выбор категории -->
    <div class="filters">
      <div class="filter-block">
        <label class="filter-label">Категория:</label>
        <div class="checkbox-group">
          <label
            v-for="category in categories"
            :key="category.id"
            :class="{ disabled: !category.active }"
          >
            <input
              type="radio"
              name="category"
              :value="category.id"
              v-model="selectedCategory"
              :disabled="!category.active"
              @change="fetchProducts"
            />
            {{ category.name }}
          </label>
        </div>
      </div>

      <!-- Фильтры по атрибутам -->
      <div
        v-for="attr in filteredAttributeNames"
        :key="attr"
        class="filter-block"
      >
        <label class="filter-label">{{ attr }}:</label>
        <div class="checkbox-group">
          <label
            v-for="option in filterOptions[attr]"
            :key="option.value"
            :class="{ disabled: !option.active }"
          >
            <input
              type="checkbox"
              :value="option.value"
              :disabled="!option.active"
              v-model="filters[attr]"
              @change="fetchProducts"
            />
            {{ option.value }}
          </label>
        </div>
      </div>

      <!-- Диапазон цены -->
      <div
        class="filter-block"
        v-if="selectedCategory"
      >
        <label class="filter-label">Цена, грн:</label>
        <div class="price-range">
          <input
            type="number"
            v-model.number="priceRange.min"
            @input="fetchProducts"
            placeholder="Мин"
            class="price-input"
            min="0"
          />
          <span class="separator">–</span>
          <input
            type="number"
            v-model.number="priceRange.max"
            @input="fetchProducts"
            placeholder="Макс"
            class="price-input"
            min="0"
          />
        </div>
      </div>
    </div>

    <!-- Товары -->
    <div class="products" v-if="selectedCategory">
      <div
        v-for="product in products"
        :key="product.id"
        class="product-card"
      >
        <img
          :src="product.image || (product.images[0]?.url || '')"
          alt="product image"
          class="product-image"
        />
        <h2
          class="product-name"
          :title="product.name"
        >{{ product.name }}</h2>
        <p class="product-description">
          {{ product.description?.substring(0, 70) }}...
        </p>
        <p class="product-price">{{ product.price }} грн</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    const attributeNames = [
      'Цвет', 'Размер', 'Минимальное количество участников', 'Количество участников', 'Тип',
      'Тип лампы', 'Состояние', 'Материал', 'Вес', 'Ширина', 'Длина', 'Высота',
      'Емкость аккумулятора', 'Интерфейс', 'Дисплей', 'Рабочая частота', 'Коэффициент усиления',
      'Тип коннектора', 'Количество в комплекте', 'Возрастная группа', 'Тематика костюма',
      'Жанр', 'Тип картины', 'Упаковка', 'Ориентация', 'Тип аккумулятора', 'Инструмент для сборки',
      'Угол обзора', 'Тип подсвечника', 'Вид свечи', 'Пол', 'Форма', 'Вид брелка', 'Тип пепельницы',
      'Назначение', 'Принцип работы', 'Форма вазы', 'Применение', 'Форма светильника', 'Тип фотоаппарата'
    ];

    const filters = {};
    for (const attr of attributeNames) {
      filters[attr] = [];
    }

    return {
      products: [],
      categories: [],
      selectedCategory: null,
      attributeNames,
      filters,
      filterOptions: {},
      priceRange: { min: '', max: '' },
    };
  },

  computed: {
    filteredAttributeNames() {
      if (!this.selectedCategory) return [];
      return this.attributeNames.filter(attr =>
        this.filterOptions[attr] &&
        this.filterOptions[attr].some(option => option.active)
      );
    }
  },

  async mounted() {
    await this.fetchCategories();
  },

  methods: {
    async fetchCategories() {
      const response = await axios.get('/api/v1/categories');
      this.categories = response.data.data.map(cat => ({
        ...cat,
        active: true,
      }));
    },

    async fetchProducts() {
  // Если категории нет — очищаем всё
        if (!this.selectedCategory) {
            this.products = [];
            this.filterOptions = {};
            for (const key in this.filters) {
            this.filters[key] = [];
            }
            return;
        }

        // Очистить фильтры перед загрузкой новой категории
        this.filterOptions = {};
        for (const key in this.filters) {
            this.filters[key] = [];
        }

        const params = {
            category_id: this.selectedCategory,
            per_page: 100,
        };

        for (const attr of this.attributeNames) {
            if (this.filters[attr].length > 0) {
            params[`filter[${attr}]`] = this.filters[attr].join(',');
            }
        }

        if (this.priceRange.min !== '' && !isNaN(this.priceRange.min)) {
            params['min_price'] = this.priceRange.min;
        }
        if (this.priceRange.max !== '' && !isNaN(this.priceRange.max)) {
            params['max_price'] = this.priceRange.max;
        }

        const response = await axios.get('/api/v1/products', { params });
        this.products = response.data.data || [];

        this.updateFilterOptions(this.products);
        },

    updateFilterOptions(products) {
      const optionsMap = {};
      for (const attr of this.attributeNames) {
        optionsMap[attr] = new Map();
        this.filterOptions[attr] = this.filterOptions[attr] || [];
        for (const option of this.filterOptions[attr]) {
          optionsMap[attr].set(option.value, { value: option.value, active: false });
        }
      }

      for (const product of products) {
        for (const attr of product.attributes || []) {
          const name = attr.name;
          const value = attr.pivot?.value || attr.value;
          if (optionsMap[name]) {
            if (!optionsMap[name].has(value)) {
              optionsMap[name].set(value, { value, active: true });
            } else {
              optionsMap[name].get(value).active = true;
            }
          }
        }
      }

      for (const attr of this.attributeNames) {
        this.filterOptions[attr] = Array.from(optionsMap[attr].values())
          .sort((a, b) => a.value.localeCompare(b.value));
      }
    },

    updateCategoryActivity() {
      const activeCategoryIds = new Set(this.products.map(p => p.category_id));
      this.categories = this.categories.map(cat => ({
        ...cat,
        active: activeCategoryIds.has(cat.id),
      }));

      if (this.selectedCategory && !activeCategoryIds.has(this.selectedCategory)) {
        this.selectedCategory = null;
      }
    }
  }
};
</script>

<style>
.container {
  max-width: 1120px;
  margin: 0 auto;
  padding: 24px;
  font-family: Arial, sans-serif;
  color: #333;
}

.title {
  font-size: 28px;
  font-weight: bold;
  margin-bottom: 24px;
}

.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  max-height: 260px;
  overflow-y: auto;
  border: 1px solid #ccc;
  padding: 16px;
  border-radius: 6px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

.filter-block {
  display: flex;
  flex-direction: column;
  width: 200px;
}

.filter-label {
  margin-bottom: 6px;
  font-weight: 600;
  color: #555;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  max-height: 120px;
  overflow-y: auto;
  border: 1px solid #ddd;
  padding: 4px 8px;
  border-radius: 4px;
  background: #fafafa;
}

.checkbox-group label {
  cursor: pointer;
  margin-bottom: 4px;
  user-select: none;
}

.checkbox-group label.disabled {
  color: #999;
  cursor: not-allowed;
}

.checkbox-group input[type="checkbox"],
.checkbox-group input[type="radio"] {
  margin-right: 8px;
}

.price-range {
  display: flex;
  align-items: center;
}

.price-input {
  width: 70px;
  padding: 6px 8px;
  border: 1px solid #bbb;
  border-radius: 4px;
  font-size: 14px;
  color: #222;
  outline: none;
  transition: border-color 0.3s ease;
}

.price-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 5px rgba(59, 130, 246, 0.5);
}

.separator {
  margin: 0 8px;
  font-weight: 700;
  user-select: none;
}

.products {
  margin-top: 32px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 24px;
}

.product-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 1px 5px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  transition: box-shadow 0.3s ease;
}

.product-card:hover {
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-image {
  width: 100%;
  height: 160px;
  object-fit: cover;
  border-radius: 6px;
  margin-bottom: 12px;
}

.product-name {
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 8px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-description {
  font-size: 13px;
  color: #666;
  flex-grow: 1;
  margin-bottom: 12px;
}

.product-price {
  font-weight: 700;
  color: #1e40af;
  font-size: 16px;
  margin-top: auto;
}


</style>