async function getWordPressData() {
  try {
    const response = await fetch('https://https://sasapay.co.ke/wp-json/wp/v2/posts');
    const posts = await response.json();
    return posts;
  } catch (error) {
    console.error("Fetch error:", error);
    return [];
  }
}
