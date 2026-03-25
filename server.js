const path = require('path');
const express = require('express');
const session = require('express-session');
const { query } = require('./config/db');

const app = express();
const PORT = process.env.PORT || 3000;

// Сессии (пока те же по смыслу, что в PHP)
app.use(session({
  secret: 'kriter-secret-key',
  resave: false,
  saveUninitialized: false,
  cookie: { secure: false },
}));

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Статика: CSS, картинки, JS из папки public
app.use(express.static(path.join(__dirname, 'public')));

// ——— Шаг 1: главная страница (как index.php) ———
app.get('/', async (req, res) => {
  try {
    const specialOffers = await query(`
      SELECT m.product_id, m.name, m.description, m.price, m.image_url
      FROM menu m
      JOIN special_offers s ON m.product_id = s.product_id
      ORDER BY s.position
    `);

    const popularRows = await query(`
      SELECT m.product_id, m.name, m.image_url, m.description, COUNT(od.product_id) as order_count
      FROM menu m
      JOIN order_details od ON m.product_id = od.product_id
      GROUP BY m.product_id
      ORDER BY order_count DESC
      LIMIT 1
    `);
    const popularProduct = popularRows[0] || null;

    res.render('index', {
      specialOffers,
      popularProduct,
      user: req.session.user || null,
    });
  } catch (err) {
    console.error('Ошибка главной:', err);
    res.status(500).send('Ошибка загрузки главной страницы.');
  }
});

app.listen(PORT, () => {
  console.log(`Сервер: http://localhost:${PORT}`);
});
