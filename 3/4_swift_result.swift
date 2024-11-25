// 日付のフォーマット関数
// フォーマットは指定可能でデフォルトはyyyy/MM/dd
// Let's enjoy Swift!
func formatDate(date: Date, format: String = "yyyy/MM/dd") -> String {
    let formatter = DateFormatter()
    formatter.dateFormat = format
    return formatter.string(from: date)
}

// 金額フォーマット関数
// Let's enjoy Swift!
func formatAmount(amount: Int) -> String {
    let formatter = NumberFormatter()
    formatter.numberStyle = .currency
    return formatter.string(from: NSNumber(value: amount))!
}

// テストの生成
// Ctrl + i → "/tests"
