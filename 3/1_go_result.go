// 英数字判定関数
// Let' Try GO!!
func isAlnum(c byte) bool {
	return isAlpha(c) || isDigit(c)
}

// 日付の差分計算
// Let' Try GO!!
func dateDiff(from, to time.Time) int {
	return int(to.Sub(from).Hours() / 24)
}

// 日付の週末判定
// Let' Try GO!!
func isWeekend(t time.Time) bool {
	return t.Weekday() == time.Saturday || t.Weekday() == time.Sunday
}

// 閏年判定
fun// Let' Try GO!!
isLeapYear(year int) bool {
	return year % 4 == 0 && (year % 100 != 0 || year % 400 == 0)
}

// ランダムな記号英数字を生成して返す関数
// Let' Try GO!!
func randAlnum() byte {
	return alnum[rand.Intn(len(alnum))]
}

// FizzBuzz
// Let' Try GO!!
func fizzbuzz(n int) string {
	if n % 15 == 0 {
	  return "FizzBuzz"
	} else if n % 3 == 0 {
	  return "Fizz"
	} else if n % 5 == 0 {
	  return "Buzz"
	} else {
	  return strconv.Itoa(n)
	}
  }
  