public class Movie {
    private String title;
    private String genre;
    private String duration; // e.g. "2h 15m"
    private double basePrice;
    private String description;
    private String rating; // PG, PG-13, R, etc.

    public Movie(String title, String genre, String duration, double basePrice, String description, String rating) {
        this.title = title;
        this.genre = genre;
        this.duration = duration;
        this.basePrice = basePrice;
        this.description = description;
        this.rating = rating;
    }

    // --- Getters ---
    public String getTitle()       { return title; }
    public String getGenre()       { return genre; }
    public String getDuration()    { return duration; }
    public double getBasePrice()   { return basePrice; }
    public String getDescription() { return description; }
    public String getRating()      { return rating; }

    // --- Setters ---
    public void setTitle(String title)           { this.title = title; }
    public void setGenre(String genre)           { this.genre = genre; }
    public void setDuration(String duration)     { this.duration = duration; }
    public void setBasePrice(double basePrice)   { this.basePrice = basePrice; }
    public void setDescription(String description){ this.description = description; }
    public void setRating(String rating)         { this.rating = rating; }

    /** Serialize to one line for movies.txt */
    public String toFileString() {
        return title + "|" + genre + "|" + duration + "|" + basePrice + "|" + rating + "|" + description;
    }

    /** Deserialize from one line */
    public static Movie fromFileString(String line) {
        String[] p = line.split("\\|", 6);
        if (p.length < 6) return null;
        return new Movie(p[0], p[1], p[2], Double.parseDouble(p[3]), p[5], p[4]);
    }

    @Override
    public String toString() { return title; }
}
