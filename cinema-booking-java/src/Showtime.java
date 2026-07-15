public class Showtime {
    private final String id;
    private final String movieTitle;
    private final String date;
    private final String time;
    private final int hall;

    public Showtime(String id, String movieTitle, String date, String time, int hall) {
        this.id = id;
        this.movieTitle = movieTitle;
        this.date = date;
        this.time = time;
        this.hall = hall;
    }

    public static Showtime create(String movieTitle, String date, String time, int hall) {
        return new Showtime("ST" + System.currentTimeMillis(), movieTitle, date, time, hall);
    }

    public String getId()         { return id; }
    public String getMovieTitle() { return movieTitle; }
    public String getDate()       { return date; }
    public String getTime()       { return time; }
    public int    getHall()       { return hall; }
    public String getDisplay()    { return date + "  " + time + "  |  Hall " + hall; }

    public String toFileString() {
        return id + "|" + movieTitle + "|" + date + "|" + time + "|" + hall;
    }

    public static Showtime fromFileString(String line) {
        String[] p = line.split("\\|", 5);
        if (p.length < 5) return null;
        try {
            return new Showtime(p[0], p[1], p[2], p[3], Integer.parseInt(p[4]));
        } catch (NumberFormatException e) { return null; }
    }

    @Override
    public String toString() { return getDisplay(); }
}
