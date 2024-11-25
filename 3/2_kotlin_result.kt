// ランダムな記号英数字を生成して返す関数
// 生成する文字数は引数で指定する
// 補間が出てこないのはプレーンテキスト扱いになっているため(右下をチェック)
// func入力 → タブ


/**
 * 指定された長さのランダムな文字列を生成する関数。
 *
 * @param length 生成する文字列の長さ
 * @return 生成されたランダムな文字列
 */
func randomString(length: Int) -> String {
    let letters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
    return String((0..<length).map{ _ in letters.randomElement()! })
}


// FizzBuzz
/**
 * FizzBuzz問題を解く関数。
 *
 * @param n FizzBuzzを実行する範囲の上限
 */
fun fizzBuzz(n: Int) {
    for (i in 1..n) {
        when {
            i % 15 == 0 -> println("FizzBuzz")
            i % 3 == 0 -> println("Fizz")
            i % 5 == 0 -> println("Buzz")
            else -> println(i)
        }
    }
}