import mysql.connector
from flask import Flask, jsonify

app = Flask(__name__)

# Connect to database
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="COWS@123fly",
    database="lpg_order_system"
)

@app.route('/test-db')
def test_db():
    cursor = db.cursor()
    cursor.execute("SELECT DATABASE();")
    result = cursor.fetchone()
    return jsonify({"connected_to": result[0]})

if __name__ == '__main__':
    app.run(debug=True)
