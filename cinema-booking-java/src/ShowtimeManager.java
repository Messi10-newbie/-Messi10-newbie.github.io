import java.io.*;
import java.util.*;
import java.util.stream.*;

public class ShowtimeManager {

    private static final String FILE = "showtimes.txt";

    public static void initDefaults() {
        File f = new File(FILE);
        if (!f.exists()) {
            List<Movie> movies = MovieManager.loadAll();
            List<Showtime> defaults = new ArrayList<>();
            String[] dates = {"2026-04-28", "2026-04-29", "2026-04-30"};
            String[] times = {"10:00", "13:30", "17:00", "20:30"};
            int hall = 1;
            long base = System.currentTimeMillis();
            int idx = 0;
            for (Movie m : movies) {
                for (String date : dates) {
                    for (String t : times) {
                        Showtime st = new Showtime("ST" + (base + idx++), m.getTitle(), date, t, hall);
                        defaults.add(st);
                        SeatManager.initSeats(st.getId());
                        hall = (hall % 5) + 1;
                    }
                }
            }
            saveAll(defaults);
        }
    }

    public static List<Showtime> loadAll() {
        List<Showtime> list = new ArrayList<>();
        try (BufferedReader br = new BufferedReader(new FileReader(FILE))) {
            String line;
            while ((line = br.readLine()) != null) {
                if (!line.isBlank()) {
                    Showtime s = Showtime.fromFileString(line.trim());
                    if (s != null) list.add(s);
                }
            }
        } catch (IOException ignored) {}
        return list;
    }

    public static void saveAll(List<Showtime> list) {
        try (PrintWriter pw = new PrintWriter(new FileWriter(FILE))) {
            for (Showtime s : list) pw.println(s.toFileString());
        } catch (IOException e) { e.printStackTrace(); }
    }

    public static void add(Showtime s) {
        List<Showtime> list = loadAll();
        list.add(s);
        saveAll(list);
        SeatManager.initSeats(s.getId());
    }

    public static void delete(String id) {
        List<Showtime> list = loadAll();
        list.removeIf(s -> s.getId().equals(id));
        saveAll(list);
    }

    public static List<Showtime> getByMovie(String movieTitle) {
        return loadAll().stream()
                .filter(s -> s.getMovieTitle().equals(movieTitle))
                .sorted(Comparator.comparing(Showtime::getDate).thenComparing(Showtime::getTime))
                .collect(Collectors.toList());
    }

    public static Showtime findById(String id) {
        return loadAll().stream().filter(s -> s.getId().equals(id)).findFirst().orElse(null);
    }

    public static int totalAvailable(String movieTitle) {
        return getByMovie(movieTitle).stream()
                .mapToInt(s -> SeatManager.availableCount(s.getId()))
                .sum();
    }

    public static void updateMovieTitle(String oldTitle, String newTitle) {
        List<Showtime> updated = loadAll().stream().map(s ->
            s.getMovieTitle().equals(oldTitle)
                ? new Showtime(s.getId(), newTitle, s.getDate(), s.getTime(), s.getHall())
                : s
        ).collect(Collectors.toList());
        saveAll(updated);
    }
}
