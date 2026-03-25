/**
 * Подключение к MySQL — те же настройки, что в public/db.php.
 * БД не меняем.
 */
const mysql = require('mysql2/promise');

const dbConfig = {
  host: 'mysql-8.0',
  user: 'root',
  password: '',
  database: 'kriter',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
};

let pool = null;

function getPool() {
  if (!pool) {
    pool = mysql.createPool(dbConfig);
  }
  return pool;
}

async function query(sql, params = []) {
  const pool = getPool();
  const [rows] = await pool.execute(sql, params);
  return rows;
}

module.exports = {
  getPool,
  query,
};
